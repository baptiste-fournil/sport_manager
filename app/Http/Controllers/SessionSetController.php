<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionSetRequest;
use App\Http\Requests\UpdateSessionSetRequest;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionSetController extends Controller
{
    /**
     * Store a newly created set for a session exercise.
     */
    public function store(StoreSessionSetRequest $request, SessionExercise $sessionExercise): RedirectResponse
    {
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
                    'rest_seconds_actual' => $validated['rest_seconds_actual'],
                ]);
            }
        }

        return back()->with('success', 'Set added successfully!');
    }

    /**
     * Update an existing set.
     */
    public function update(UpdateSessionSetRequest $request, SessionSet $sessionSet): RedirectResponse
    {
        // Verify user owns this session
        if ($sessionSet->sessionExercise->trainingSession->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();

        $sessionSet->update([
            'reps' => $validated['reps'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'distance' => $validated['distance'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Set updated successfully!');
    }

    /**
     * Mark a set as completed (sets completed_at timestamp).
     */
    public function complete(Request $request, SessionSet $sessionSet): RedirectResponse
    {
        // Verify user owns this session
        if ($sessionSet->sessionExercise->trainingSession->user_id !== $request->user()->id) {
            abort(403);
        }

        $sessionSet->update([
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Set completed!');
    }

    /**
     * Remove a set and reindex remaining sets.
     */
    public function destroy(Request $request, SessionSet $sessionSet): RedirectResponse
    {
        // Verify user owns this session
        if ($sessionSet->sessionExercise->trainingSession->user_id !== $request->user()->id) {
            abort(403);
        }

        DB::transaction(function () use ($sessionSet) {
            $sessionExerciseId = $sessionSet->session_exercise_id;
            $deletedSetIndex = $sessionSet->set_index;

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

        return back()->with('success', 'Set deleted successfully!');
    }
}
