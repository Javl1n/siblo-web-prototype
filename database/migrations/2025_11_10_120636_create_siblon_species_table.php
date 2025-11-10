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
        Schema::create('siblon_species', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->integer('evolution_stage')->default(1);
            $table->foreignId('evolves_from_id')->nullable()->constrained('siblon_species')->nullOnDelete();
            $table->integer('evolution_level_required')->nullable();
            $table->integer('base_hp')->default(50);
            $table->integer('base_attack')->default(10);
            $table->integer('base_defense')->default(10);
            $table->string('sprite_url', 500)->nullable();
            $table->boolean('is_starter')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('is_starter');
            $table->index('evolution_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siblon_species');
    }
};
