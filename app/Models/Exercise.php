<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'muscle_group',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingExercises(): HasMany
    {
        return $this->hasMany(TrainingExercise::class);
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class);
    }
}
