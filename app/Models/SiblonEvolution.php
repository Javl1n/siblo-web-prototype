<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiblonEvolution extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'player_siblon_id',
        'from_species_id',
        'to_species_id',
        'evolved_at_level',
        'evolved_at',
    ];

    protected function casts(): array
    {
        return [
            'evolved_at_level' => 'integer',
            'evolved_at' => 'datetime',
        ];
    }

    /**
     * Get the Siblon that evolved.
     */
    public function playerSiblon(): BelongsTo
    {
        return $this->belongsTo(PlayerSiblon::class);
    }

    /**
     * Get the species evolved from.
     */
    public function fromSpecies(): BelongsTo
    {
        return $this->belongsTo(SiblonSpecies::class, 'from_species_id');
    }

    /**
     * Get the species evolved to.
     */
    public function toSpecies(): BelongsTo
    {
        return $this->belongsTo(SiblonSpecies::class, 'to_species_id');
    }
}
