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
}
