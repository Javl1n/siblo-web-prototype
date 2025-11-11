<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        return abort(403, 'Students should use the game client to access SIBLO.');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('quizzes', [App\Http\Controllers\Teacher\QuizController::class, 'index'])
        ->name('quizzes.index');
    Route::get('quizzes/create', [App\Http\Controllers\Teacher\QuizController::class, 'create'])
        ->name('quizzes.create');
    Route::post('quizzes', [App\Http\Controllers\Teacher\QuizController::class, 'store'])
        ->name('quizzes.store');
    Route::get('quizzes/{quiz}', [App\Http\Controllers\Teacher\QuizController::class, 'show'])
        ->name('quizzes.show');
    Route::get('quizzes/{quiz}/edit', [App\Http\Controllers\Teacher\QuizController::class, 'edit'])
        ->name('quizzes.edit');
    Route::put('quizzes/{quiz}', [App\Http\Controllers\Teacher\QuizController::class, 'update'])
        ->name('quizzes.update');
    Route::delete('quizzes/{quiz}', [App\Http\Controllers\Teacher\QuizController::class, 'destroy'])
        ->name('quizzes.destroy');
    Route::post('quizzes/{quiz}/toggle-publish', [App\Http\Controllers\Teacher\QuizController::class, 'togglePublish'])
        ->name('quizzes.toggle-publish');

    Route::post('ai/generate-quiz', [App\Http\Controllers\Teacher\AiQuizGenerationController::class, 'generate'])
        ->name('ai.generate-quiz');
    Route::post('ai/upload-module', [App\Http\Controllers\Teacher\AiQuizGenerationController::class, 'uploadModule'])
        ->name('ai.upload-module');

    Route::get('students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])
        ->name('students.index');
    Route::get('students/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])
        ->name('students.show');
});

require __DIR__.'/settings.php';
