<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;

class TrainingSessionPolicy {
  /**
   * Determine whether the user can view any training sessions.
   */
  public function viewAny(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can view the training session.
   */
  public function view(User $user, TrainingSession $session): bool {
    return $user->id === $session->user_id;
  }

  /**
   * Determine whether the user can create training sessions.
   */
  public function create(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can update the training session.
   */
  public function update(User $user, TrainingSession $session): bool {
    return $user->id === $session->user_id;
  }

  /**
   * Determine whether the user can delete the training session.
   */
  public function delete(User $user, TrainingSession $session): bool {
    return $user->id === $session->user_id;
  }
}
