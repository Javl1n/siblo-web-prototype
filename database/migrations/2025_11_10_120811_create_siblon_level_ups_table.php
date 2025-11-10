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
        Schema::create('siblon_level_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_siblon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->integer('old_level');
            $table->integer('new_level');
            $table->integer('experience_gained');
            $table->integer('hp_increased')->default(0);
            $table->integer('attack_increased')->default(0);
            $table->integer('defense_increased')->default(0);
            $table->timestamp('leveled_up_at')->useCurrent();

            $table->index('player_siblon_id');
            $table->index('quiz_attempt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siblon_level_ups');
    }
};
