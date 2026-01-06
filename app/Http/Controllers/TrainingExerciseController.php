<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Training;
use App\Models\TrainingExercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingExerciseController extends Controller {
  /**
   * Add an exercise to a training.
   */
  public function store(Request $request, Training $training): JsonResponse {
    $this->authorize('update', $training);

    $validated = $request->validate([
      'exercise_id' => ['required', 'exists:exercises,id'],
      'default_sets' => ['nullable', 'integer', 'min:1', 'max:20'],
      'default_reps' => ['nullable', 'integer', 'min:1', 'max:500'],
      'default_rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
      'notes' => ['nullable', 'string', 'max:1000'],
    ]);

    // Verify the exercise belongs to the user
    $exercise = Exercise::where('id', $validated['exercise_id'])
      ->where('user_id', $request->user()->id)
      ->firstOrFail();

    // Get the highest order_index and add 1
    $maxOrder = $training->trainingExercises()->max('order_index') ?? -1;

    $trainingExercise = $training->trainingExercises()->create([
      'exercise_id' => $exercise->id,
      'order_index' => $maxOrder + 1,
      'default_sets' => $validated['default_sets'] ?? null,
      'default_reps' => $validated['default_reps'] ?? null,
      'default_rest_seconds' => $validated['default_rest_seconds'] ?? 90,
      'notes' => $validated['notes'] ?? null,
    ]);

    $trainingExercise->load('exercise');

    return response()->json([
      'message' => 'Exercise added successfully.',
      'training_exercise' => $trainingExercise,
    ], 201);
  }

  /**
   * Update a training exercise's details.
   */
  public function update(Request $request, TrainingExercise $trainingExercise): JsonResponse {
    $this->authorize('update', $trainingExercise->training);

    $validated = $request->validate([
      'default_sets' => ['nullable', 'integer', 'min:1', 'max:20'],
      'default_reps' => ['nullable', 'integer', 'min:1', 'max:500'],
      'default_rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
      'notes' => ['nullable', 'string', 'max:1000'],
    ]);

    $trainingExercise->update($validated);
    $trainingExercise->load('exercise');

    return response()->json([
      'message' => 'Exercise updated successfully.',
      'training_exercise' => $trainingExercise,
    ]);
  }

  /**
   * Reorder exercises within a training.
   */
  public function reorder(Request $request, Training $training): JsonResponse {
    $this->authorize('update', $training);

    $validated = $request->validate([
      'exercises' => ['required', 'array'],
      'exercises.*.id' => ['required', 'exists:training_exercises,id'],
      'exercises.*.order_index' => ['required', 'integer', 'min:0'],
    ]);

    DB::transaction(function () use ($validated, $training) {
      foreach ($validated['exercises'] as $exerciseData) {
        $trainingExercise = TrainingExercise::where('id', $exerciseData['id'])
          ->where('training_id', $training->id)
          ->firstOrFail();

        $trainingExercise->update([
          'order_index' => $exerciseData['order_index'],
        ]);
      }
    });

    return response()->json([
      'message' => 'Exercises reordered successfully.',
    ]);
  }

  /**
   * Remove an exercise from a training.
   */
  public function destroy(TrainingExercise $trainingExercise): JsonResponse|RedirectResponse {
    $this->authorize('update', $trainingExercise->training);

    $trainingId = $trainingExercise->training_id;
    $trainingExercise->delete();

    // If this is an AJAX request, return JSON
    if (request()->wantsJson()) {
      return response()->json([
        'message' => 'Exercise removed successfully.',
      ]);
    }

    // Otherwise redirect back
    return redirect()->route('trainings.show', $trainingId)
      ->with('success', 'Exercise removed successfully.');
  }
}
