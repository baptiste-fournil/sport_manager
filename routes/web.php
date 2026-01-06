<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingExerciseController;
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
});

require __DIR__ . '/auth.php';
