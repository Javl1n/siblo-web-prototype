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
        Schema::create('quiz_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('experience_points_earned')->default(0);
            $table->integer('coins_earned')->default(0);
            $table->foreignId('siblon_caught_species_id')->nullable()->constrained('siblon_species')->nullOnDelete();
            $table->foreignId('siblon_caught_id')->nullable()->constrained('player_siblons')->nullOnDelete();
            $table->boolean('rewards_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('quiz_attempt_id');
            $table->index('rewards_claimed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_rewards');
    }
};
