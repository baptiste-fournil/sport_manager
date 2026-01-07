<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\Training;
use App\Models\TrainingExercise;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    // Get the first user
    $user = User::first();

    if (! $user) {
      $this->command->error('No user found. Please create a user first.');

      return;
    }

    $this->command->info("Seeding demo data for user: {$user->name}");

    // Create exercises
    $exercises = $this->createExercises($user);
    $this->command->info('Created ' . count($exercises) . ' exercises');

    // Create training templates
    $trainings = $this->createTrainingTemplates($user, $exercises);
    $this->command->info('Created ' . count($trainings) . ' training templates');

    // Create training sessions with realistic data
    $this->createTrainingSessions($user, $exercises, $trainings);
    $this->command->info('Created training sessions with performance data');

    $this->command->info('✅ Demo data seeded successfully!');
  }

  /**
   * Create a variety of exercises.
   */
  private function createExercises(User $user): array {
    $exercises = [
      // Strength - Upper Body
      ['name' => 'Bench Press', 'type' => 'strength', 'muscle_group' => 'Chest', 'description' => 'Barbell bench press for chest development'],
      ['name' => 'Incline Dumbbell Press', 'type' => 'strength', 'muscle_group' => 'Chest', 'description' => 'Upper chest focus with dumbbells'],
      ['name' => 'Pull-ups', 'type' => 'strength', 'muscle_group' => 'Back', 'description' => 'Bodyweight back exercise'],
      ['name' => 'Barbell Row', 'type' => 'strength', 'muscle_group' => 'Back', 'description' => 'Bent over row for back thickness'],
      ['name' => 'Overhead Press', 'type' => 'strength', 'muscle_group' => 'Shoulders', 'description' => 'Standing shoulder press'],
      ['name' => 'Lateral Raises', 'type' => 'strength', 'muscle_group' => 'Shoulders', 'description' => 'Dumbbell side raises'],
      ['name' => 'Barbell Curl', 'type' => 'strength', 'muscle_group' => 'Biceps', 'description' => 'Standard bicep curl'],
      ['name' => 'Tricep Dips', 'type' => 'strength', 'muscle_group' => 'Triceps', 'description' => 'Bodyweight tricep exercise'],

      // Strength - Lower Body
      ['name' => 'Barbell Squat', 'type' => 'strength', 'muscle_group' => 'Legs', 'description' => 'King of leg exercises'],
      ['name' => 'Romanian Deadlift', 'type' => 'strength', 'muscle_group' => 'Hamstrings', 'description' => 'Hamstring focused deadlift'],
      ['name' => 'Leg Press', 'type' => 'strength', 'muscle_group' => 'Legs', 'description' => 'Machine leg press'],
      ['name' => 'Walking Lunges', 'type' => 'strength', 'muscle_group' => 'Legs', 'description' => 'Dynamic leg exercise'],
      ['name' => 'Leg Curl', 'type' => 'strength', 'muscle_group' => 'Hamstrings', 'description' => 'Machine hamstring curl'],
      ['name' => 'Calf Raises', 'type' => 'strength', 'muscle_group' => 'Calves', 'description' => 'Standing calf raises'],

      // Core
      ['name' => 'Plank', 'type' => 'strength', 'muscle_group' => 'Core', 'description' => 'Isometric core hold'],
      ['name' => 'Russian Twists', 'type' => 'strength', 'muscle_group' => 'Core', 'description' => 'Rotational core exercise'],
      ['name' => 'Hanging Leg Raises', 'type' => 'strength', 'muscle_group' => 'Core', 'description' => 'Advanced ab exercise'],

      // Cardio
      ['name' => 'Running', 'type' => 'cardio', 'muscle_group' => null, 'description' => 'Outdoor or treadmill running'],
      ['name' => 'Cycling', 'type' => 'cardio', 'muscle_group' => null, 'description' => 'Stationary or road cycling'],
      ['name' => 'Rowing Machine', 'type' => 'cardio', 'muscle_group' => null, 'description' => 'Full body cardio'],

      // Flexibility
      ['name' => 'Hamstring Stretch', 'type' => 'flexibility', 'muscle_group' => 'Hamstrings', 'description' => 'Static hamstring stretching'],
      ['name' => 'Shoulder Stretch', 'type' => 'flexibility', 'muscle_group' => 'Shoulders', 'description' => 'Shoulder mobility'],
    ];

    $createdExercises = [];
    foreach ($exercises as $exerciseData) {
      $createdExercises[] = Exercise::create([
        'user_id' => $user->id,
        'name' => $exerciseData['name'],
        'type' => $exerciseData['type'],
        'muscle_group' => $exerciseData['muscle_group'],
        'description' => $exerciseData['description'],
      ]);
    }

    return $createdExercises;
  }

  /**
   * Create training templates.
   */
  private function createTrainingTemplates(User $user, array $exercises): array {
    $trainings = [];

    // Get exercises by name for easy reference
    $exercisesByName = collect($exercises)->keyBy('name');

    // Push Day (Chest, Shoulders, Triceps)
    $pushDay = Training::create([
      'user_id' => $user->id,
      'name' => 'Push Day',
      'description' => 'Chest, shoulders, and triceps workout',
      'notes' => 'Focus on progressive overload',
    ]);

    TrainingExercise::create(['training_id' => $pushDay->id, 'exercise_id' => $exercisesByName['Bench Press']->id, 'order_index' => 0, 'default_rest_seconds' => 180]);
    TrainingExercise::create(['training_id' => $pushDay->id, 'exercise_id' => $exercisesByName['Incline Dumbbell Press']->id, 'order_index' => 1, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $pushDay->id, 'exercise_id' => $exercisesByName['Overhead Press']->id, 'order_index' => 2, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $pushDay->id, 'exercise_id' => $exercisesByName['Lateral Raises']->id, 'order_index' => 3, 'default_rest_seconds' => 90]);
    TrainingExercise::create(['training_id' => $pushDay->id, 'exercise_id' => $exercisesByName['Tricep Dips']->id, 'order_index' => 4, 'default_rest_seconds' => 90]);

    $trainings[] = $pushDay;

    // Pull Day (Back, Biceps)
    $pullDay = Training::create([
      'user_id' => $user->id,
      'name' => 'Pull Day',
      'description' => 'Back and biceps workout',
      'notes' => 'Focus on back width and thickness',
    ]);

    TrainingExercise::create(['training_id' => $pullDay->id, 'exercise_id' => $exercisesByName['Pull-ups']->id, 'order_index' => 0, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $pullDay->id, 'exercise_id' => $exercisesByName['Barbell Row']->id, 'order_index' => 1, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $pullDay->id, 'exercise_id' => $exercisesByName['Barbell Curl']->id, 'order_index' => 2, 'default_rest_seconds' => 90]);

    $trainings[] = $pullDay;

    // Leg Day
    $legDay = Training::create([
      'user_id' => $user->id,
      'name' => 'Leg Day',
      'description' => 'Complete lower body workout',
      'notes' => 'Never skip leg day!',
    ]);

    TrainingExercise::create(['training_id' => $legDay->id, 'exercise_id' => $exercisesByName['Barbell Squat']->id, 'order_index' => 0, 'default_rest_seconds' => 180]);
    TrainingExercise::create(['training_id' => $legDay->id, 'exercise_id' => $exercisesByName['Romanian Deadlift']->id, 'order_index' => 1, 'default_rest_seconds' => 150]);
    TrainingExercise::create(['training_id' => $legDay->id, 'exercise_id' => $exercisesByName['Leg Press']->id, 'order_index' => 2, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $legDay->id, 'exercise_id' => $exercisesByName['Leg Curl']->id, 'order_index' => 3, 'default_rest_seconds' => 90]);
    TrainingExercise::create(['training_id' => $legDay->id, 'exercise_id' => $exercisesByName['Calf Raises']->id, 'order_index' => 4, 'default_rest_seconds' => 60]);

    $trainings[] = $legDay;

    // Full Body
    $fullBody = Training::create([
      'user_id' => $user->id,
      'name' => 'Full Body Strength',
      'description' => 'Complete body workout for efficiency',
      'notes' => 'Great for beginners or time-constrained days',
    ]);

    TrainingExercise::create(['training_id' => $fullBody->id, 'exercise_id' => $exercisesByName['Barbell Squat']->id, 'order_index' => 0, 'default_rest_seconds' => 150]);
    TrainingExercise::create(['training_id' => $fullBody->id, 'exercise_id' => $exercisesByName['Bench Press']->id, 'order_index' => 1, 'default_rest_seconds' => 150]);
    TrainingExercise::create(['training_id' => $fullBody->id, 'exercise_id' => $exercisesByName['Barbell Row']->id, 'order_index' => 2, 'default_rest_seconds' => 120]);
    TrainingExercise::create(['training_id' => $fullBody->id, 'exercise_id' => $exercisesByName['Overhead Press']->id, 'order_index' => 3, 'default_rest_seconds' => 120]);

    $trainings[] = $fullBody;

    return $trainings;
  }

  /**
   * Create realistic training sessions over the past 90 days.
   */
  private function createTrainingSessions(User $user, array $exercises, array $trainings): void {
    $exercisesByName = collect($exercises)->keyBy('name');

    // Create sessions over the last 90 days with progressive overload
    $startDate = Carbon::now()->subDays(90);

    // Session counter for variety
    $sessionCount = 0;

    // Create 30 completed sessions (roughly 2-3 per week)
    for ($i = 0; $i < 30; $i++) {
      $training = $trainings[$sessionCount % count($trainings)];

      // Space sessions 2-4 days apart
      $daysAgo = 90 - ($i * 3) - rand(0, 2);
      $startedAt = Carbon::now()->subDays($daysAgo)->setTime(rand(9, 18), rand(0, 59));

      $session = TrainingSession::create([
        'user_id' => $user->id,
        'training_id' => $training->id,
        'name' => $training->name,
        'notes' => $i % 5 === 0 ? 'Felt great today!' : null,
        'started_at' => $startedAt,
        'completed_at' => $startedAt->copy()->addMinutes(rand(45, 75)),
      ]);

      // Add exercises based on the training template
      $trainingExercises = $training->trainingExercises()->with('exercise')->orderBy('order_index')->get();

      foreach ($trainingExercises as $index => $trainingExercise) {
        $sessionExercise = SessionExercise::create([
          'training_session_id' => $session->id,
          'exercise_id' => $trainingExercise->exercise_id,
          'order_index' => $index,
        ]);

        // Create progressive sets with realistic weights
        $this->createSetsForExercise($sessionExercise, $trainingExercise->exercise, $i, $trainingExercise->default_rest_seconds);
      }

      $sessionCount++;
    }

    // Create 1 in-progress session (most recent)
    $training = $trainings[0];
    $startedAt = Carbon::now()->subHours(rand(1, 3));

    $session = TrainingSession::create([
      'user_id' => $user->id,
      'training_id' => $training->id,
      'name' => $training->name . ' (In Progress)',
      'notes' => null,
      'started_at' => $startedAt,
      'completed_at' => null,
    ]);

    // Add only first 2 exercises (session in progress)
    $trainingExercises = $training->trainingExercises()->with('exercise')->orderBy('order_index')->limit(2)->get();

    foreach ($trainingExercises as $index => $trainingExercise) {
      $sessionExercise = SessionExercise::create([
        'training_session_id' => $session->id,
        'exercise_id' => $trainingExercise->exercise_id,
        'order_index' => $index,
      ]);

      // Create only 2-3 sets (incomplete)
      $this->createSetsForExercise($sessionExercise, $trainingExercise->exercise, 30, $trainingExercise->default_rest_seconds, rand(2, 3));
    }
  }

  /**
   * Create realistic sets for an exercise with progressive overload.
   */
  private function createSetsForExercise(SessionExercise $sessionExercise, Exercise $exercise, int $progressionIndex, int $defaultRest, ?int $maxSets = null): void {
    // Determine number of sets
    $numSets = $maxSets ?? rand(3, 4);

    // Base weights for different exercises (with progression)
    $baseWeights = [
      'Bench Press' => 60 + ($progressionIndex * 0.5),
      'Incline Dumbbell Press' => 20 + ($progressionIndex * 0.3),
      'Barbell Squat' => 80 + ($progressionIndex * 0.7),
      'Romanian Deadlift' => 70 + ($progressionIndex * 0.5),
      'Barbell Row' => 60 + ($progressionIndex * 0.4),
      'Overhead Press' => 40 + ($progressionIndex * 0.3),
      'Pull-ups' => 0, // Bodyweight
      'Barbell Curl' => 30 + ($progressionIndex * 0.2),
      'Leg Press' => 120 + ($progressionIndex * 1.0),
      'Lateral Raises' => 10 + ($progressionIndex * 0.1),
      'Tricep Dips' => 0, // Bodyweight
      'Leg Curl' => 40 + ($progressionIndex * 0.3),
      'Calf Raises' => 50 + ($progressionIndex * 0.3),
    ];

    $weight = $baseWeights[$exercise->name] ?? 50;

    // Determine rep range based on exercise type
    $repRanges = [
      'Bench Press' => [6, 8],
      'Barbell Squat' => [5, 8],
      'Barbell Row' => [8, 10],
      'Overhead Press' => [6, 10],
      'Pull-ups' => [6, 12],
      'Incline Dumbbell Press' => [8, 12],
      'Barbell Curl' => [10, 12],
      'Lateral Raises' => [12, 15],
    ];

    [$minReps, $maxReps] = $repRanges[$exercise->name] ?? [8, 12];

    for ($setIndex = 0; $setIndex < $numSets; $setIndex++) {
      // Slight fatigue in later sets
      $reps = rand($minReps, $maxReps) - floor($setIndex / 2);
      $setWeight = $weight - ($setIndex * 2.5); // Slight weight drop in later sets

      // Rest time variation (shorter for last set)
      $actualRest = $setIndex < $numSets - 1
        ? $defaultRest + rand(-10, 20)
        : null;

      SessionSet::create([
        'session_exercise_id' => $sessionExercise->id,
        'set_index' => $setIndex,
        'reps' => $reps > 0 ? $reps : 1,
        'weight' => $setWeight > 0 ? round($setWeight, 1) : null,
        'duration_seconds' => null,
        'distance' => null,
        'notes' => $setIndex === 0 && rand(1, 10) === 1 ? 'New PR!' : null,
        'completed_at' => Carbon::now(),
        'rest_seconds_actual' => $actualRest,
      ]);
    }
  }
}
