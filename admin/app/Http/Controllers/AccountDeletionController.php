<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FcmToken;
use App\Models\PushNotificationRecipient;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use App\Models\UserDailyProgress;
use App\Models\UserProgression;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccountDeletionController extends Controller
{
    /**
     * Display the public Account Deletion request page (required by Google Play Store).
     */
    public function show(Request $request): View
    {
        $prefilledEmail = $request->query('email', '');

        return view('legal.delete-account', compact('prefilledEmail'));
    }

    /**
     * Process the account deletion request.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'confirmation' => ['accepted'],
        ], [
            'email.required' => 'Please enter the email address associated with your Quizs account.',
            'email.email' => 'Please provide a valid email address.',
            'confirmation.accepted' => 'You must check the confirmation box to confirm permanent account deletion.',
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::where('email', $email)->first();

        if ($user) {
            // Protect administrative accounts from public web deletion
            if ($user->role === 'admin') {
                return back()
                    ->withInput()
                    ->with('error', 'Administrator accounts cannot be deleted through this public form. Please manage administrative privileges via the Admin Console.');
            }

            DB::transaction(function () use ($user) {
                // 1. Revoke all Sanctum API personal access tokens
                $user->tokens()->delete();

                // 2. Delete quiz attempt answers and attempts
                $attemptIds = QuizAttempt::where('user_id', $user->id)->pluck('id');
                if ($attemptIds->isNotEmpty()) {
                    QuizAttemptAnswer::whereIn('quiz_attempt_id', $attemptIds)->delete();
                    QuizAttempt::whereIn('id', $attemptIds)->delete();
                }

                // 3. Delete notification tokens and received notification logs
                PushNotificationRecipient::where('user_id', $user->id)->delete();
                FcmToken::where('user_id', $user->id)->delete();

                // 4. Delete user activity sessions, streak progression, daily progress, attendance
                UserSession::where('user_id', $user->id)->delete();
                UserProgression::where('user_id', $user->id)->delete();
                UserDailyProgress::where('user_id', $user->id)->delete();
                Attendance::where('user_id', $user->id)->delete();

                // 5. Audit log
                Log::info('User account permanently deleted via public deletion portal', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toIso8601String(),
                ]);

                // 6. Delete user record
                $user->delete();
            });

            return back()->with('success', "Your account ({$email}) and all associated profile, quiz history, scores, and streak data have been permanently deleted from Quizs.");
        }

        // Return a reassuring response even if not found to satisfy Play Console guidelines without exposing user existence
        return back()->with('success', "If an active account associated with {$email} was registered on Quizs, it and all associated data have been permanently removed.");
    }
}
