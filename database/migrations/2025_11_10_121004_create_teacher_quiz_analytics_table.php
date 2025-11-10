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
        Schema::create('teacher_quiz_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_attempts')->default(0);
            $table->integer('total_completions')->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->integer('average_time_seconds')->nullable();
            $table->integer('highest_score')->nullable();
            $table->integer('lowest_score')->nullable();
            $table->timestamp('last_updated')->useCurrent();

            $table->unique(['quiz_id', 'teacher_id']);
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_quiz_analytics');
    }
};
