<?php

use App\Http\Controllers\LegalController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseMigrationController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\FirebaseSettingsController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizAttemptController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\TurnstileSettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to admin dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Public legal pages — linked directly from the Flutter app's profile screen.
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');

// Admin Authentication (Public)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Admin Protected Routes (Role: admin)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    Route::resource('categories', CategoryController::class);
    Route::resource('quizzes', QuizController::class);
    Route::resource('questions', QuestionController::class);
    Route::resource('quiz-attempts', QuizAttemptController::class)->only(['index', 'show', 'destroy']);
    Route::get('quiz-attempts/{quizAttempt}/summary/pdf', [QuizAttemptController::class, 'summaryPdf'])->name('quiz-attempts.summary.pdf');
    Route::get('quiz-attempts/{quizAttempt}/summary/csv', [QuizAttemptController::class, 'summaryCsv'])->name('quiz-attempts.summary.csv');
    Route::get('quiz-attempts-summary/pdf', [QuizAttemptController::class, 'summaryListPdf'])->name('quiz-attempts.summary-list.pdf');
    Route::get('quiz-attempts-summary/csv', [QuizAttemptController::class, 'summaryListCsv'])->name('quiz-attempts.summary-list.csv');
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::put('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');

        Route::get('/email', [EmailSettingsController::class, 'edit'])->name('email');
        Route::put('/email', [EmailSettingsController::class, 'update'])->name('email.update');
        Route::post('/email/test', [EmailSettingsController::class, 'sendTest'])->name('email.test');

        Route::get('/firebase', [FirebaseSettingsController::class, 'edit'])->name('firebase');
        Route::put('/firebase', [FirebaseSettingsController::class, 'update'])->name('firebase.update');

        Route::get('/turnstile', [TurnstileSettingsController::class, 'edit'])->name('turnstile');
        Route::put('/turnstile', [TurnstileSettingsController::class, 'update'])->name('turnstile.update');

        Route::get('/migrations', [DatabaseMigrationController::class, 'index'])->name('migrations');
        Route::post('/migrations/run', [DatabaseMigrationController::class, 'run'])->name('migrations.run');
    });

    Route::get('targets', [TargetController::class, 'index'])->name('targets.index');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::resource('push-notifications', PushNotificationController::class)->only(['index', 'create', 'store', 'show']);
});
