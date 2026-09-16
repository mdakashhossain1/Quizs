<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
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
     * Log in an existing, verified user.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

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

        if (! $user->otp_code || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This code has expired. Please request a new one.',
            ], 422);
        }

        if (! Hash::check($request->code, $user->otp_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 422);
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
     * Log out and revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }
}
