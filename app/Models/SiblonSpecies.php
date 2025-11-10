<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiblonSpecies extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'evolution_stage',
        'evolves_from_id',
        'evolution_level_required',
        'base_hp',
        'base_attack',
        'base_defense',
        'sprite_url',
        'is_starter',
    ];

    protected function casts(): array
    {
        return [
            'evolution_stage' => 'integer',
            'evolution_level_required' => 'integer',
            'base_hp' => 'integer',
            'base_attack' => 'integer',
            'base_defense' => 'integer',
            'is_starter' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the species this Siblon evolves from.
     */
    public function evolvesFrom(): BelongsTo
    {
        return $this->belongsTo(SiblonSpecies::class, 'evolves_from_id');
    }

    /**
     * Get the species that evolve from this Siblon.
     */
    public function evolvesInto(): HasMany
    {
        return $this->hasMany(SiblonSpecies::class, 'evolves_from_id');
    }

    /**
     * Get all player instances of this species.
     */
    public function playerSiblons(): HasMany
    {
        return $this->hasMany(PlayerSiblon::class);
    }

    /**
     * Scope a query to only include starter Siblons.
     */
    public function scopeStarters($query)
    {
        return $query->where('is_starter', true);
    }

    /**
     * Scope a query to only include Siblons at a specific evolution stage.
     */
    public function scopeEvolutionStage($query, int $stage)
    {
        return $query->where('evolution_stage', $stage);
    }

    /**
     * Check if this species can evolve.
     */
    public function canEvolve(): bool
    {
        return $this->evolvesInto()->exists();
    }

    /**
     * Get the next evolution for this species.
     */
    public function nextEvolution(): ?SiblonSpecies
    {
        return $this->evolvesInto()->first();
    }
}
