<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy {
  /**
   * Determine whether the user can view any exercises.
   */
  public function viewAny(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can view the exercise.
   */
  public function view(User $user, Exercise $exercise): bool {
    return $user->id === $exercise->user_id;
  }

  /**
   * Determine whether the user can create exercises.
   */
  public function create(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can update the exercise.
   */
  public function update(User $user, Exercise $exercise): bool {
    return $user->id === $exercise->user_id;
  }

  /**
   * Determine whether the user can delete the exercise.
   */
  public function delete(User $user, Exercise $exercise): bool {
    return $user->id === $exercise->user_id;
  }
}
