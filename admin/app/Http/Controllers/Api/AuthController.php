<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * How long a generated OTP code stays valid.
     */
    private const OTP_TTL_MINUTES = 5;

    /**
     * Register a new user via API and send an email OTP to verify it.
     * No auth token is issued until the OTP is confirmed.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'streak' => 1,
            'score' => 0,
            'is_active' => true,
        ]);

        $this->issueOtp($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. A verification code has been sent to your email.',
            'email' => $user->email,
        ], 201);
    }

    /**
     * Log in an existing, verified user. The identifier may be either the
     * account's email address or the login ID an admin assigned it.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $request->string('email')->toString();
        $user = User::where('email', $identifier)->orWhere('login_id', $identifier)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been deactivated. Please contact support.',
            ], 403);
        }

        if (! $user->email_verified_at) {
            $this->issueOtp($user);

            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first. A new verification code has been sent.',
                'requires_verification' => true,
                'email' => $user->email,
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
            'must_change_password' => $user->must_change_password,
            'heartbeat_interval_seconds' => config('quiz.heartbeat_interval_seconds'),
        ]);
    }

    /**
     * Set a new password for an account that was created by an admin with a
     * temporary one (or had one issued via an admin-initiated reset). Any
     * other authenticated endpoint is blocked until this succeeds.
     */
    public function forceChangePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => false,
        ])->save();

        // Revoke the token issued at temp-password login and replace it with
        // a full-access one now that the account is no longer restricted.
        // Null-safe: currentAccessToken() is null under actingAs()-style
        // sessions with no real PersonalAccessToken (see logout()).
        $user->currentAccessToken()?->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully.',
            'token' => $token,
            'user' => $user,
            'must_change_password' => false,
            'heartbeat_interval_seconds' => config('quiz.heartbeat_interval_seconds'),
        ]);
    }

    /**
     * (Re)send a fresh OTP code to an existing, unverified account.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already verified.',
            ], 422);
        }

        $this->issueOtp($user);

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.',
        ]);
    }

    /**
     * Confirm an OTP code and activate the account with a login token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($error = $this->checkOtp($user, $request->code)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Request a password-reset OTP for an existing account.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();
        $this->issueOtp($user);

        return response()->json([
            'success' => true,
            'message' => 'A password reset code has been sent to your email.',
        ]);
    }

    /**
     * Confirm a password-reset OTP and set a new password, logging the user in.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($error = $this->checkOtp($user, $validated['code'])) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Validates a submitted OTP code against the stored hash and expiry,
     * returning a user-facing error message, or null when it checks out.
     */
    private function checkOtp(User $user, string $code): ?string
    {
        if (! $user->otp_code || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return 'This code has expired. Please request a new one.';
        }

        if (! Hash::check($code, $user->otp_code)) {
            return 'Invalid verification code.';
        }

        return null;
    }

    /**
     * Generate a fresh 4-digit OTP for the user, store it hashed, and email it.
     */
    private function issueOtp(User $user): void
    {
        $code = (string) random_int(1000, 9999);

        $user->forceFill([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ])->save();

        Mail::to($user->email)->send(new OtpMail($code, self::OTP_TTL_MINUTES));
    }

    /**
     * Handle Google sign-in from the mobile / web client. The client's Firebase
     * ID token is independently verified against Google's Identity Toolkit so
     * the email/name used to find-or-create the account can't be spoofed.
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $verified = $this->verifyFirebaseIdToken($request->id_token);

        if (! $verified) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired Google sign-in token.',
            ], 401);
        }

        $email = $verified['email'];
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $verified['name'] ?? explode('@', $email)[0],
                'email' => $email,
                'email_verified_at' => now(), // Google already verified this address.
                'password' => Hash::make(Str::random(24)),
                'role' => 'user',
                'google_id' => $verified['localId'],
                'avatar' => $verified['photoUrl'],
                'streak' => 1,
                'score' => 0,
                'is_active' => true,
            ]);
        } else {
            $updated = false;
            if ($verified['localId'] && ! $user->google_id) {
                $user->google_id = $verified['localId'];
                $updated = true;
            }
            if ($verified['photoUrl'] && ! $user->avatar) {
                $user->avatar = $verified['photoUrl'];
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        $token = $user->createToken('google-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Google authentication successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Verifies a Firebase ID token via the Identity Toolkit REST API and
     * returns the server-confirmed identity, or null if it doesn't check out.
     *
     * @return array{email: string, name: ?string, localId: ?string, photoUrl: ?string}|null
     */
    private function verifyFirebaseIdToken(string $idToken): ?array
    {
        $apiKey = config('services.firebase.api_key');
        if (! $apiKey) {
            return null;
        }

        $response = Http::post(
            "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
            ['idToken' => $idToken],
        );

        if (! $response->successful()) {
            return null;
        }

        $account = $response->json('users.0');
        if (! $account || empty($account['email']) || ! ($account['emailVerified'] ?? false)) {
            return null;
        }

        return [
            'email' => $account['email'],
            'name' => $account['displayName'] ?? null,
            'localId' => $account['localId'] ?? null,
            'photoUrl' => $account['photoUrl'] ?? null,
        ];
    }

    /**
     * Get authenticated user profile and stats.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadCount('quizAttempts');

        return response()->json([
            'success' => true,
            'user' => $user,
            'heartbeat_interval_seconds' => config('quiz.heartbeat_interval_seconds'),
        ]);
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar' => ['sometimes', 'nullable', 'string'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Log out and revoke the current access token. When the client passes
     * the device id its heartbeats have been using, the matching activity
     * session is closed with an explicit-logout timestamp rather than being
     * left to expire via the online timeout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $deviceId = $request->string('device_id')->toString();
        if ($deviceId !== '') {
            UserSession::where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->where('status', 'active')
                ->update([
                    'explicit_logout_at' => now(),
                    'status' => 'ended',
                ]);
        }

        // Null-safe: currentAccessToken() is only null for a request
        // authenticated some other way than a real personal-access token
        // (e.g. a stateful session guard, or an actingAs()'d test) — nothing
        // to revoke in that case, but it must not crash the logout call.
        $user->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }
}
