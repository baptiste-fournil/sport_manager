<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionExercise extends Model {
    protected $fillable = [
        'training_session_id',
        'exercise_id',
        'order_index',
        'notes',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function trainingSession(): BelongsTo {
        return $this->belongsTo(TrainingSession::class);
    }

    public function exercise(): BelongsTo {
        return $this->belongsTo(Exercise::class);
    }

    public function sessionSets(): HasMany {
        return $this->hasMany(SessionSet::class)->orderBy('set_index');
    }
}
