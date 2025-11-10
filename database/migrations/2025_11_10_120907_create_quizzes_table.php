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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard']);
            $table->integer('total_points')->default(0);
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->text('ai_generation_prompt')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('difficulty_level');
            $table->index('is_active');
            $table->index('is_published');
            $table->index('created_by');
            $table->index('is_ai_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
