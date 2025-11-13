<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizReward extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quiz_attempt_id',
        'experience_points_earned',
        'coins_earned',
        'siblon_caught_species_id',
        'siblon_caught_id',
        'rewards_claimed',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'experience_points_earned' => 'integer',
            'coins_earned' => 'integer',
            'rewards_claimed' => 'boolean',
            'claimed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the quiz attempt these rewards are for.
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    /**
     * Get the species of Siblon caught (if any).
     */
    public function siblonCaughtSpecies(): BelongsTo
    {
        return $this->belongsTo(SiblonSpecies::class, 'siblon_caught_species_id');
    }

    /**
     * Get the Siblon caught (if any).
     */
    public function siblonCaught(): BelongsTo
    {
        return $this->belongsTo(PlayerSiblon::class, 'siblon_caught_id');
    }

    /**
     * Claim the rewards and distribute them to the player.
     */
    public function claim(): void
    {
        if ($this->rewards_claimed) {
            return;
        }

        $playerProfile = $this->quizAttempt->playerProfile;

        $playerProfile->addExperience($this->experience_points_earned);
        $playerProfile->addCoins($this->coins_earned);

        $this->rewards_claimed = true;
        $this->claimed_at = now();
        $this->save();
    }
}
