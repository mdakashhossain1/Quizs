<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizAttemptController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TargetController;
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

    Route::resource('categories', CategoryController::class);
    Route::resource('quizzes', QuizController::class);
    Route::resource('questions', QuestionController::class);
    Route::resource('quiz-attempts', QuizAttemptController::class)->only(['index', 'show', 'destroy']);
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('targets', [TargetController::class, 'index'])->name('targets.index');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::resource('push-notifications', PushNotificationController::class)->only(['index', 'create', 'store', 'show']);
});
