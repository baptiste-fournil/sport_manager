<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionSet extends Model {
    protected $fillable = [
        'session_exercise_id',
        'set_index',
        'reps',
        'weight',
        'duration_seconds',
        'distance',
        'rest_seconds_actual',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'set_index' => 'integer',
        'reps' => 'integer',
        'weight' => 'decimal:2',
        'duration_seconds' => 'integer',
        'distance' => 'decimal:2',
        'rest_seconds_actual' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function sessionExercise(): BelongsTo {
        return $this->belongsTo(SessionExercise::class);
    }

    public function isCompleted(): bool {
        return $this->completed_at !== null;
    }
}
