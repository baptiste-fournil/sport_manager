<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingExercise extends Model
{
    protected $fillable = [
        'training_id',
        'exercise_id',
        'order_index',
        'default_sets',
        'default_reps',
        'default_rest_seconds',
        'notes',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'default_sets' => 'integer',
        'default_reps' => 'integer',
        'default_rest_seconds' => 'integer',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
