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
        Schema::create('battle_states', function (Blueprint $table) {
            $table->id();
            $table->uuid('battle_id')->unique();
            $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('player1_siblon_id')->constrained('player_siblons')->onDelete('cascade');
            $table->foreignId('player2_siblon_id')->nullable()->constrained('player_siblons')->onDelete('cascade');
            $table->integer('current_turn')->default(1);
            $table->foreignId('turn_player_id')->constrained('users');
            $table->integer('player1_hp');
            $table->integer('player2_hp');
            $table->enum('battle_type', ['pvp', 'pve', 'training'])->default('training');
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->foreignId('winner_id')->nullable()->constrained('users');
            $table->json('battle_log')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('battle_id');
            $table->index(['player1_id', 'player2_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_states');
    }
};
