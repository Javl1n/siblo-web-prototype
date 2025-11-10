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
        Schema::create('siblon_evolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_siblon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_species_id')->constrained('siblon_species')->cascadeOnDelete();
            $table->foreignId('to_species_id')->constrained('siblon_species')->cascadeOnDelete();
            $table->integer('evolved_at_level');
            $table->timestamp('evolved_at')->useCurrent();

            $table->index('player_siblon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siblon_evolutions');
    }
};
