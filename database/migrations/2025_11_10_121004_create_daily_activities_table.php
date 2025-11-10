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
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_profile_id')->constrained()->cascadeOnDelete();
            $table->date('activity_date');
            $table->integer('quizzes_completed')->default(0);
            $table->integer('total_experience_earned')->default(0);
            $table->integer('total_coins_earned')->default(0);
            $table->integer('siblons_caught')->default(0);
            $table->integer('time_played_minutes')->default(0);
            $table->timestamps();

            $table->unique(['player_profile_id', 'activity_date']);
            $table->index('player_profile_id');
            $table->index('activity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activities');
    }
};
