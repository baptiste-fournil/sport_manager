<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_exercise_id')->constrained()->cascadeOnDelete();
            $table->integer('set_index')->default(1);
            $table->integer('reps')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // kg or lbs
            $table->integer('duration_seconds')->nullable(); // for time-based exercises
            $table->decimal('distance', 8, 2)->nullable(); // km or miles
            $table->integer('rest_seconds_actual')->nullable(); // actual rest taken before next set
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['session_exercise_id', 'set_index']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_sets');
    }
};
