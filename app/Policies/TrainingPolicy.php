<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;

class TrainingPolicy {
  /**
   * Determine whether the user can view any trainings.
   */
  public function viewAny(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can view the training.
   */
  public function view(User $user, Training $training): bool {
    return $user->id === $training->user_id;
  }

  /**
   * Determine whether the user can create trainings.
   */
  public function create(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can update the training.
   */
  public function update(User $user, Training $training): bool {
    return $user->id === $training->user_id;
  }

  /**
   * Determine whether the user can delete the training.
   */
  public function delete(User $user, Training $training): bool {
    return $user->id === $training->user_id;
  }
}
