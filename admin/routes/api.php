<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizApiController;
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

// Public Quiz & Content Browsing
Route::get('/categories', [QuizApiController::class, 'categories']);
Route::get('/categories/{id}/quizzes', [QuizApiController::class, 'quizzesByCategory']);
Route::get('/quizzes/{id}', [QuizApiController::class, 'quizDetail']);
Route::get('/leaderboard', [QuizApiController::class, 'leaderboard']);

// Protected User & Quiz Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::post('/quizzes/{id}/submit', [QuizApiController::class, 'submitQuiz']);
    Route::get('/user/history', [QuizApiController::class, 'userHistory']);
});
