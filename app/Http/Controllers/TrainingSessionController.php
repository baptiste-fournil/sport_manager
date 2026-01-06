<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionStoreRequest;
use App\Models\Training;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TrainingSessionController extends Controller {
  /**
   * Display the session start page with available trainings.
   */
  public function start(Request $request) {
    $user = $request->user();

    // Get user's trainings with exercise count
    $trainings = Training::where('user_id', $user->id)
      ->withCount('trainingExercises')
      ->orderBy('updated_at', 'desc')
      ->get()
      ->map(function ($training) {
        return [
          'id' => $training->id,
          'name' => $training->name,
          'description' => $training->description,
          'exercise_count' => $training->training_exercises_count,
          'updated_at' => $training->updated_at,
        ];
      });

    return Inertia::render('Sessions/Start', [
      'trainings' => $trainings,
    ]);
  }

  /**
   * Store a new training session.
   * Creates session from training template or blank session.
   */
  public function store(SessionStoreRequest $request) {
    $user = $request->user();
    $validated = $request->validated();

    try {
      $session = DB::transaction(function () use ($validated, $user) {
        // Determine session name
        $sessionName = null;
        $trainingId = $validated['training_id'] ?? null;

        if ($trainingId) {
          // Get training to copy name and exercises
          $training = Training::where('id', $trainingId)
            ->where('user_id', $user->id)
            ->with(['trainingExercises' => function ($query) {
              $query->orderBy('order_index');
            }])
            ->firstOrFail();

          $sessionName = $training->name;
        } else {
          // Blank session - use provided name
          $sessionName = $validated['name'];
        }

        // Create training session
        $session = TrainingSession::create([
          'user_id' => $user->id,
          'training_id' => $trainingId,
          'name' => $sessionName,
          'notes' => $validated['notes'] ?? null,
          'started_at' => now(),
          'completed_at' => null, // Session in progress
        ]);

        // Copy training exercises to session exercises (if template used)
        if ($trainingId && isset($training)) {
          foreach ($training->trainingExercises as $trainingExercise) {
            $session->sessionExercises()->create([
              'exercise_id' => $trainingExercise->exercise_id,
              'order_index' => $trainingExercise->order_index,
              'notes' => $trainingExercise->notes,
            ]);
          }
        }

        return $session;
      });

      return redirect()
        ->route('sessions.show', $session)
        ->with('success', 'Training session started successfully!');
    } catch (\Exception $e) {
      return back()
        ->withErrors(['error' => 'Failed to start session. Please try again.'])
        ->withInput();
    }
  }

  /**
   * Display the specified training session.
   */
  public function show(Request $request, TrainingSession $session) {
    // Authorize: ensure user owns the session
    if ($session->user_id !== $request->user()->id) {
      abort(403, 'Unauthorized access to this session.');
    }

    // Load session with exercises and exercise details
    $session->load([
      'training',
      'sessionExercises' => function ($query) {
        $query->orderBy('order_index');
      },
      'sessionExercises.exercise',
      'sessionExercises.sessionSets' => function ($query) {
        $query->orderBy('set_index');
      },
    ]);

    return Inertia::render('Sessions/Show', [
      'session' => [
        'id' => $session->id,
        'name' => $session->name,
        'notes' => $session->notes,
        'started_at' => $session->started_at,
        'completed_at' => $session->completed_at,
        'is_completed' => $session->isCompleted(),
        'is_in_progress' => $session->isInProgress(),
        'training' => $session->training ? [
          'id' => $session->training->id,
          'name' => $session->training->name,
        ] : null,
        'exercises' => $session->sessionExercises->map(function ($sessionExercise) {
          return [
            'id' => $sessionExercise->id,
            'order_index' => $sessionExercise->order_index,
            'notes' => $sessionExercise->notes,
            'exercise' => [
              'id' => $sessionExercise->exercise->id,
              'name' => $sessionExercise->exercise->name,
              'type' => $sessionExercise->exercise->type,
              'muscle_group' => $sessionExercise->exercise->muscle_group,
            ],
            'sets' => $sessionExercise->sessionSets->map(function ($set) {
              return [
                'id' => $set->id,
                'set_index' => $set->set_index,
                'reps' => $set->reps,
                'weight' => $set->weight,
                'duration_seconds' => $set->duration_seconds,
                'distance' => $set->distance,
                'rest_seconds_actual' => $set->rest_seconds_actual,
                'notes' => $set->notes,
                'completed_at' => $set->completed_at,
                'is_completed' => $set->isCompleted(),
              ];
            }),
          ];
        }),
      ],
    ]);
  }
}
