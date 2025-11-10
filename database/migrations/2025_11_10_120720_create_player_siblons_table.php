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
        Schema::create('player_siblons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('siblon_species_id')->constrained()->cascadeOnDelete();
            $table->string('nickname', 50)->nullable();
            $table->integer('level')->default(1);
            $table->integer('experience_points')->default(0);
            $table->integer('current_hp');
            $table->integer('max_hp');
            $table->integer('attack');
            $table->integer('defense');
            $table->boolean('is_in_party')->default(true);
            $table->timestamp('obtained_at')->useCurrent();
            $table->timestamps();

            $table->index('player_profile_id');
            $table->index(['player_profile_id', 'is_in_party']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_siblons');
    }
};
