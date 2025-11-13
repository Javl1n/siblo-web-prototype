<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\QuizController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are for the PixiJS game client. All routes use Sanctum
| authentication except for login and registration endpoints.
|
*/

// Authentication Routes (Public)
Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected API Routes (Require Sanctum Authentication)
Route::middleware('auth:sanctum')->group(function (): void {
    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Player Profile
    Route::prefix('player')->group(function (): void {
        Route::get('profile', [PlayerController::class, 'profile']);
        Route::get('siblons', [PlayerController::class, 'siblons']);
        Route::get('daily-activity', [PlayerController::class, 'dailyActivity']);
    });

    // Quizzes
    Route::prefix('quizzes')->group(function (): void {
        Route::get('/', [QuizController::class, 'index']);
        Route::get('{quiz}', [QuizController::class, 'show']);
        Route::post('{quiz}/start', [QuizController::class, 'start']);
    });

    // Quiz Attempts
    Route::post('quiz-attempts/{quizAttempt}/submit', [QuizController::class, 'submit']);

    // Battles
    Route::prefix('battles')->group(function (): void {
        Route::post('start', [BattleController::class, 'start']);
        Route::get('{battle}', [BattleController::class, 'show']);
        Route::post('{battle}/forfeit', [BattleController::class, 'forfeit']);
    });
});
