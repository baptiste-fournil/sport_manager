<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionSetRequest;
use App\Http\Requests\UpdateSessionSetRequest;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionSetApiController extends Controller {
  /**
   * Get session data with all relationships.
   */
  public function getSession(Request $request, TrainingSession $session): JsonResponse {
    // Verify user owns this session
    if ($session->user_id !== $request->user()->id) {
      abort(403);
    }

    $session->load([
      'training',
      'exercises.exercise',
      'exercises.sessionSets' => fn($q) => $q->orderBy('set_index'),
    ]);

    return response()->json([
      'session' => $session,
    ]);
  }

  /**
   * Store a newly created set for a session exercise.
   */
  public function store(StoreSessionSetRequest $request, SessionExercise $sessionExercise): JsonResponse {
    // Verify user owns this session
    if ($sessionExercise->trainingSession->user_id !== $request->user()->id) {
      abort(403);
    }

    $validated = $request->validated();

    // Get the next set index
    $nextSetIndex = $sessionExercise->sessionSets()->max('set_index') + 1;

    // Create the set
    $set = $sessionExercise->sessionSets()->create([
      'set_index' => $nextSetIndex,
      'reps' => $validated['reps'] ?? null,
      'weight' => $validated['weight'] ?? null,
      'duration_seconds' => $validated['duration_seconds'] ?? null,
      'distance' => $validated['distance'] ?? null,
      'notes' => $validated['notes'] ?? null,
      'completed_at' => now(),
    ]);

    // If there's a previous set and we have rest time, update it
    if (isset($validated['rest_seconds_actual']) && $nextSetIndex > 1) {
      $previousSet = $sessionExercise->sessionSets()
        ->where('set_index', $nextSetIndex - 1)
        ->first();

      if ($previousSet) {
        $previousSet->update([
          'rest_seconds_actual' => $validated['rest_seconds_actual']
        ]);
      }
    }

    // Return the created set
    return response()->json([
      'set' => $set,
      'message' => 'Set added successfully!',
    ], 201);
  }

  /**
   * Update an existing set.
   */
  public function update(UpdateSessionSetRequest $request, SessionSet $sessionSet): JsonResponse {
    // Verify user owns this session
    if ($sessionSet->sessionExercise->trainingSession->user_id !== $request->user()->id) {
      abort(403);
    }

    $validated = $request->validated();

    // Only update fields that are present in the request
    $updateData = [];
    if (array_key_exists('reps', $validated)) {
      $updateData['reps'] = $validated['reps'];
    }
    if (array_key_exists('weight', $validated)) {
      $updateData['weight'] = $validated['weight'];
    }
    if (array_key_exists('duration_seconds', $validated)) {
      $updateData['duration_seconds'] = $validated['duration_seconds'];
    }
    if (array_key_exists('distance', $validated)) {
      $updateData['distance'] = $validated['distance'];
    }
    if (array_key_exists('notes', $validated)) {
      $updateData['notes'] = $validated['notes'];
    }
    if (array_key_exists('rest_seconds_actual', $validated)) {
      $updateData['rest_seconds_actual'] = $validated['rest_seconds_actual'];
    }

    $sessionSet->update($updateData);

    // Reload to get fresh data
    $sessionSet->refresh();

    return response()->json([
      'set' => $sessionSet,
      'message' => 'Set updated successfully!',
    ]);
  }

  /**
   * Remove a set and reindex remaining sets.
   */
  public function destroy(Request $request, SessionSet $sessionSet): JsonResponse {
    // Verify user owns this session
    if ($sessionSet->sessionExercise->trainingSession->user_id !== $request->user()->id) {
      abort(403);
    }

    $sessionExerciseId = $sessionSet->session_exercise_id;
    $deletedSetIndex = $sessionSet->set_index;

    DB::transaction(function () use ($sessionSet, $sessionExerciseId, $deletedSetIndex) {
      // Delete the set
      $sessionSet->delete();

      // Reindex remaining sets that come after the deleted one
      SessionSet::where('session_exercise_id', $sessionExerciseId)
        ->where('set_index', '>', $deletedSetIndex)
        ->orderBy('set_index')
        ->each(function ($set) {
          $set->decrement('set_index');
        });
    });

    // Get all remaining sets for this exercise
    $remainingSets = SessionSet::where('session_exercise_id', $sessionExerciseId)
      ->orderBy('set_index')
      ->get();

    return response()->json([
      'sets' => $remainingSets,
      'message' => 'Set deleted successfully!',
    ]);
  }
}
