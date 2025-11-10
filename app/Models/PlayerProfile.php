<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trainer_name',
        'level',
        'experience_points',
        'coins',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'experience_points' => 'integer',
            'coins' => 'integer',
        ];
    }

    /**
     * Get the user that owns this player profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all Siblons owned by this player.
     */
    public function siblons(): HasMany
    {
        return $this->hasMany(PlayerSiblon::class);
    }

    /**
     * Get Siblons currently in the player's party.
     */
    public function partySiblons(): HasMany
    {
        return $this->hasMany(PlayerSiblon::class)->where('is_in_party', true);
    }

    /**
     * Get all quiz attempts by this player.
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get daily activity records for this player.
     */
    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }

    /**
     * Add experience points to the player.
     */
    public function addExperience(int $amount): void
    {
        $this->experience_points += $amount;
        $this->checkLevelUp();
        $this->save();
    }

    /**
     * Add coins to the player.
     */
    public function addCoins(int $amount): void
    {
        $this->coins += $amount;
        $this->save();
    }

    /**
     * Check if player should level up and handle it.
     */
    protected function checkLevelUp(): void
    {
        $experienceNeeded = $this->experienceNeededForNextLevel();

        while ($this->experience_points >= $experienceNeeded) {
            $this->level++;
            $experienceNeeded = $this->experienceNeededForNextLevel();
        }
    }

    /**
     * Calculate experience needed for next level.
     */
    public function experienceNeededForNextLevel(): int
    {
        return $this->level * 100;
    }
}
