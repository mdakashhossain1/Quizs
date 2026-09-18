<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\CronController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google-login', [AuthController::class, 'googleLogin']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Webcron endpoint for HTTP-based cron services (e.g. cron-job.org) to
// drain the queue on hosts without a persistent `queue:work` process.
// Auth is the ?key= query param, not auth:sanctum — see CronController.
Route::get('/cron/run-queue', [CronController::class, 'runQueue']);

// Public Quiz & Content Browsing
Route::get('/categories', [QuizApiController::class, 'categories']);
Route::get('/categories/{id}/quizzes', [QuizApiController::class, 'quizzesByCategory']);
Route::get('/quizzes/{id}', [QuizApiController::class, 'quizDetail']);
Route::get('/leaderboard', [QuizApiController::class, 'leaderboard']);

// Protected User & Quiz Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/force-change-password', [AuthController::class, 'forceChangePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('activity')->group(function () {
        Route::post('/heartbeat', [ActivityController::class, 'heartbeat']);
        Route::get('/status', [ActivityController::class, 'status']);
    });

    Route::post('/device/fcm-token', [DeviceTokenController::class, 'store']);

    // Blocked for accounts still on a temporary/admin-issued password until
    // they call /auth/force-change-password (see EnsurePasswordChanged).
    Route::middleware('password.changed')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/update-profile', [AuthController::class, 'updateProfile']);
            Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        Route::post('/quizzes/{id}/start', [QuizApiController::class, 'startAttempt']);
        Route::get('/quizzes/{id}/ranking', [QuizApiController::class, 'quizRanking']);
        Route::post('/attempts/{attempt}/answer', [QuizApiController::class, 'saveAnswer']);
        Route::post('/attempts/{attempt}/submit', [QuizApiController::class, 'submitAttempt']);
        Route::get('/user/history', [QuizApiController::class, 'userHistory']);

        Route::prefix('target')->group(function () {
            Route::get('/today', [TargetController::class, 'today']);
            Route::get('/history', [TargetController::class, 'history']);
        });

        Route::get('/profile/stats', [ProfileController::class, 'stats']);
        Route::get('/achievement', [AchievementController::class, 'summary']);

        Route::prefix('attendance')->group(function () {
            Route::get('/today', [AttendanceController::class, 'today']);
            Route::get('/history', [AttendanceController::class, 'history']);
            Route::get('/summary', [AttendanceController::class, 'summary']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/read-all', [NotificationController::class, 'markAllRead']);
            Route::post('/{recipient}/read', [NotificationController::class, 'markRead']);
        });
    });
});

// ----------------------------------------------------------------
// Development-only helpers (ONLY available when APP_ENV=local)
// Use GET /api/dev/otp/{email} to retrieve the latest OTP code
// since MAIL_MAILER=log writes emails to storage/logs/laravel.log.
// ----------------------------------------------------------------
if (app()->environment('local')) {
    Route::get('/dev/otp/{email}', function (string $email) {
        $user = \App\Models\User::where('email', $email)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // The OTP email is logged with MAIL_MAILER=log.
        // In the HTML body, the 4-digit code appears on its own line inside a <div>.
        // We look for the last 4-digit number that appears after the Quizs OTP marker.
        $logPath = storage_path('logs/laravel.log');
        $otp = null;
        if (file_exists($logPath)) {
            $log = file_get_contents($logPath);
            // Match all 4-digit codes that appear alone on a line (the OTP in the email HTML)
            if (preg_match_all('/^\s*(\d{4})\s*$/m', $log, $matches)) {
                $otp = end($matches[1]);
            }
        }
        return response()->json([
            'email'          => $user->email,
            'otp_expires_at' => $user->otp_expires_at,
            'otp'            => $otp,
            'hint'           => 'Check storage/logs/laravel.log for the full OTP email content',
        ]);
    });
}

