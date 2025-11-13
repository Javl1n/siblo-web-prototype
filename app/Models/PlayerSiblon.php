<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerSiblon extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_profile_id',
        'siblon_species_id',
        'nickname',
        'level',
        'experience_points',
        'current_hp',
        'max_hp',
        'attack',
        'defense',
        'is_in_party',
        'obtained_at',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'experience_points' => 'integer',
            'current_hp' => 'integer',
            'max_hp' => 'integer',
            'attack' => 'integer',
            'defense' => 'integer',
            'is_in_party' => 'boolean',
            'obtained_at' => 'datetime',
        ];
    }

    /**
     * Get the player profile that owns this Siblon.
     */
    public function playerProfile(): BelongsTo
    {
        return $this->belongsTo(PlayerProfile::class);
    }

    /**
     * Get the species of this Siblon.
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(SiblonSpecies::class, 'siblon_species_id');
    }

    /**
     * Get level-up records for this Siblon.
     */
    public function levelUps(): HasMany
    {
        return $this->hasMany(SiblonLevelUp::class);
    }

    /**
     * Get evolution records for this Siblon.
     */
    public function evolutions(): HasMany
    {
        return $this->hasMany(SiblonEvolution::class);
    }

    /**
     * Add experience points and handle leveling up.
     */
    public function addExperience(int $amount, ?int $quizAttemptId = null): void
    {
        $oldLevel = $this->level;
        $this->experience_points += $amount;

        $experienceNeeded = $this->experienceNeededForNextLevel();

        while ($this->experience_points >= $experienceNeeded) {
            $this->levelUp($quizAttemptId);
            $experienceNeeded = $this->experienceNeededForNextLevel();
        }

        $this->save();
    }

    /**
     * Level up the Siblon.
     */
    protected function levelUp(?int $quizAttemptId = null): void
    {
        $oldLevel = $this->level;
        $this->level++;

        $hpIncrease = rand(3, 8);
        $attackIncrease = rand(1, 3);
        $defenseIncrease = rand(1, 3);

        $this->max_hp += $hpIncrease;
        $this->current_hp = $this->max_hp;
        $this->attack += $attackIncrease;
        $this->defense += $defenseIncrease;

        if ($quizAttemptId) {
            SiblonLevelUp::create([
                'player_siblon_id' => $this->id,
                'quiz_attempt_id' => $quizAttemptId,
                'old_level' => $oldLevel,
                'new_level' => $this->level,
                'experience_gained' => 0,
                'hp_increased' => $hpIncrease,
                'attack_increased' => $attackIncrease,
                'defense_increased' => $defenseIncrease,
            ]);
        }

        $this->checkEvolution();
    }

    /**
     * Check if Siblon should evolve.
     */
    protected function checkEvolution(): void
    {
        $nextEvolution = $this->species->nextEvolution();

        if ($nextEvolution && $this->level >= $nextEvolution->evolution_level_required) {
            $this->evolve($nextEvolution);
        }
    }

    /**
     * Evolve the Siblon to a new species.
     */
    public function evolve(SiblonSpecies $newSpecies): void
    {
        $oldSpeciesId = $this->siblon_species_id;

        SiblonEvolution::create([
            'player_siblon_id' => $this->id,
            'from_species_id' => $oldSpeciesId,
            'to_species_id' => $newSpecies->id,
            'evolved_at_level' => $this->level,
        ]);

        $this->siblon_species_id = $newSpecies->id;
        $this->max_hp += $newSpecies->base_hp - $this->species->base_hp;
        $this->current_hp = $this->max_hp;
        $this->attack += $newSpecies->base_attack - $this->species->base_attack;
        $this->defense += $newSpecies->base_defense - $this->species->base_defense;

        $this->save();
    }

    /**
     * Calculate experience needed for next level.
     */
    public function experienceNeededForNextLevel(): int
    {
        return $this->level * 50;
    }

    /**
     * Get display name (nickname or species name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?? $this->species->display_name;
    }

    /**
     * Heal the Siblon.
     */
    public function heal(?int $amount = null): void
    {
        $this->current_hp = $amount ? min($this->current_hp + $amount, $this->max_hp) : $this->max_hp;
        $this->save();
    }

    /**
     * Take damage.
     */
    public function takeDamage(int $amount): void
    {
        $this->current_hp = max(0, $this->current_hp - $amount);
        $this->save();
    }

    /**
     * Check if Siblon is fainted.
     */
    public function isFainted(): bool
    {
        return $this->current_hp <= 0;
    }
}
