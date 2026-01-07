<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model {
    protected $fillable = [
        'user_id',
        'training_id',
        'name',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo {
        return $this->belongsTo(Training::class);
    }

    public function sessionExercises(): HasMany {
        return $this->hasMany(SessionExercise::class)->orderBy('order_index');
    }

    public function isCompleted(): bool {
        return $this->completed_at !== null;
    }

    public function isInProgress(): bool {
        return $this->completed_at === null;
    }

    /**
     * Get the session duration in minutes.
     */
    public function getDurationMinutes(): ?int {
        if (! $this->completed_at || ! $this->started_at) {
            return null;
        }

        return (int) $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Get the total number of exercises in the session.
     */
    public function getTotalExercisesCount(): int {
        return $this->sessionExercises()->count();
    }

    /**
     * Get the total number of sets in the session.
     */
    public function getTotalSetsCount(): int {
        return $this->sessionExercises()
            ->withCount('sessionSets')
            ->get()
            ->sum('session_sets_count');
    }
}
