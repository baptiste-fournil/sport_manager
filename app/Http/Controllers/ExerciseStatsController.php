<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatsFilterRequest;
use App\Models\Exercise;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExerciseStatsController extends Controller {
  /**
   * Display performance statistics for a specific exercise.
   */
  public function show(StatsFilterRequest $request, Exercise $exercise) {
    // Authorize: user must own the exercise
    $this->authorize('view', $exercise);

    $userId = $request->user()->id;

    // Default date range: last 90 days
    $endDate = $request->input('end_date')
      ? Carbon::parse($request->input('end_date'))->endOfDay()
      : Carbon::now()->endOfDay();

    $startDate = $request->input('start_date')
      ? Carbon::parse($request->input('start_date'))->startOfDay()
      : Carbon::now()->subDays(90)->startOfDay();

    // Query 1: Max weight by date
    $maxWeightData = $this->getMaxWeightByDate($exercise->id, $userId, $startDate, $endDate);

    // Query 2: Total volume per session
    $volumeData = $this->getVolumePerSession($exercise->id, $userId, $startDate, $endDate);

    // Query 3: Average rest time
    $avgRestTime = $this->getAverageRestTime($exercise->id, $userId, $startDate, $endDate);

    // Query 4: Personal records
    $personalRecords = $this->getPersonalRecords($exercise->id, $userId);

    // Query 5: Summary stats
    $summaryStats = $this->getSummaryStats($exercise->id, $userId, $startDate, $endDate);

    return Inertia::render('Progress/Exercise', [
      'exercise' => $exercise,
      'filters' => [
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
      ],
      'maxWeightData' => $maxWeightData,
      'volumeData' => $volumeData,
      'avgRestTime' => $avgRestTime,
      'personalRecords' => $personalRecords,
      'summaryStats' => $summaryStats,
    ]);
  }

  /**
   * Get max weight achieved by date.
   */
  private function getMaxWeightByDate(int $exerciseId, int $userId, Carbon $startDate, Carbon $endDate): array {
    $results = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.weight')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->selectRaw('DATE(training_sessions.started_at) as date, MAX(session_sets.weight) as max_weight')
      ->groupBy('date')
      ->orderBy('date')
      ->get();

    return $results->map(function ($item) {
      return [
        'date' => $item->date,
        'max_weight' => (float) $item->max_weight,
      ];
    })->toArray();
  }

  /**
   * Get total volume per session.
   */
  private function getVolumePerSession(int $exerciseId, int $userId, Carbon $startDate, Carbon $endDate): array {
    $results = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.weight')
      ->whereNotNull('session_sets.reps')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->selectRaw('
                training_sessions.id as session_id,
                training_sessions.name as session_name,
                DATE(training_sessions.started_at) as date,
                SUM(session_sets.reps * session_sets.weight) as total_volume
            ')
      ->groupBy('training_sessions.id', 'training_sessions.name', 'date')
      ->orderBy('date')
      ->get();

    return $results->map(function ($item) {
      return [
        'session_id' => $item->session_id,
        'session_name' => $item->session_name,
        'date' => $item->date,
        'total_volume' => round((float) $item->total_volume, 2),
      ];
    })->toArray();
  }

  /**
   * Get average rest time for the exercise.
   */
  private function getAverageRestTime(int $exerciseId, int $userId, Carbon $startDate, Carbon $endDate): ?float {
    $result = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.rest_seconds_actual')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->selectRaw('AVG(session_sets.rest_seconds_actual) as avg_rest')
      ->first();

    return $result && $result->avg_rest ? round((float) $result->avg_rest, 0) : null;
  }

  /**
   * Get personal records for the exercise.
   */
  private function getPersonalRecords(int $exerciseId, int $userId): array {
    // Max weight ever
    $maxWeight = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.weight')
      ->selectRaw('MAX(session_sets.weight) as max_weight, session_sets.reps')
      ->orderByDesc('session_sets.weight')
      ->first();

    // Max reps in a single set
    $maxReps = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.reps')
      ->selectRaw('MAX(session_sets.reps) as max_reps, session_sets.weight')
      ->orderByDesc('session_sets.reps')
      ->first();

    // Max volume in a single set
    $maxVolume = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.weight')
      ->whereNotNull('session_sets.reps')
      ->selectRaw('session_sets.reps, session_sets.weight, (session_sets.reps * session_sets.weight) as volume')
      ->orderByDesc('volume')
      ->first();

    return [
      'max_weight' => $maxWeight ? [
        'weight' => (float) $maxWeight->max_weight,
        'reps' => $maxWeight->reps,
      ] : null,
      'max_reps' => $maxReps ? [
        'reps' => (int) $maxReps->max_reps,
        'weight' => $maxReps->weight ? (float) $maxReps->weight : null,
      ] : null,
      'max_volume' => $maxVolume ? [
        'reps' => (int) $maxVolume->reps,
        'weight' => (float) $maxVolume->weight,
        'volume' => round((float) $maxVolume->volume, 2),
      ] : null,
    ];
  }

  /**
   * Get summary statistics for the date range.
   */
  private function getSummaryStats(int $exerciseId, int $userId, Carbon $startDate, Carbon $endDate): array {
    // Total sessions containing this exercise
    $totalSessions = DB::table('session_exercises')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('training_sessions.completed_at')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->distinct()
      ->count('training_sessions.id');

    // Total sets completed
    $totalSets = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->count();

    // Total volume
    $totalVolume = DB::table('session_sets')
      ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
      ->join('training_sessions', 'session_exercises.training_session_id', '=', 'training_sessions.id')
      ->where('session_exercises.exercise_id', $exerciseId)
      ->where('training_sessions.user_id', $userId)
      ->whereNotNull('session_sets.completed_at')
      ->whereNotNull('session_sets.weight')
      ->whereNotNull('session_sets.reps')
      ->whereBetween('training_sessions.started_at', [$startDate, $endDate])
      ->selectRaw('SUM(session_sets.reps * session_sets.weight) as total_volume')
      ->first();

    return [
      'total_sessions' => $totalSessions,
      'total_sets' => $totalSets,
      'total_volume' => $totalVolume && $totalVolume->total_volume
        ? round((float) $totalVolume->total_volume, 2)
        : 0,
    ];
  }
}
