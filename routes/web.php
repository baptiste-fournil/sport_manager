<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionSetController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingExerciseController;
use App\Http\Controllers\TrainingSessionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Exercise management
    Route::resource('exercises', ExerciseController::class);

    // Training management
    Route::resource('trainings', TrainingController::class);

    // Training exercise management
    Route::post('trainings/{training}/exercises', [TrainingExerciseController::class, 'store'])
        ->name('training-exercises.store');
    Route::patch('training-exercises/{trainingExercise}', [TrainingExerciseController::class, 'update'])
        ->name('training-exercises.update');
    Route::delete('training-exercises/{trainingExercise}', [TrainingExerciseController::class, 'destroy'])
        ->name('training-exercises.destroy');
    Route::patch('trainings/{training}/exercises/reorder', [TrainingExerciseController::class, 'reorder'])
        ->name('training-exercises.reorder');

    // Training session management
    Route::get('sessions/start', [TrainingSessionController::class, 'start'])
        ->name('sessions.start');
    Route::post('sessions', [TrainingSessionController::class, 'store'])
        ->name('sessions.store');
    Route::get('sessions/{session}', [TrainingSessionController::class, 'show'])
        ->name('sessions.show');
    Route::patch('sessions/{session}/complete', [TrainingSessionController::class, 'complete'])
        ->name('sessions.complete');

    // Session set management
    Route::post('session-exercises/{sessionExercise}/sets', [SessionSetController::class, 'store'])
        ->name('session-sets.store');
    Route::patch('session-sets/{sessionSet}', [SessionSetController::class, 'update'])
        ->name('session-sets.update');
    Route::post('session-sets/{sessionSet}/complete', [SessionSetController::class, 'complete'])
        ->name('session-sets.complete');
    Route::delete('session-sets/{sessionSet}', [SessionSetController::class, 'destroy'])
        ->name('session-sets.destroy');
});

require __DIR__ . '/auth.php';
