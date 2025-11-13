<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuizAttempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'player_profile_id',
        'started_at',
        'completed_at',
        'score',
        'total_points',
        'score_percentage',
        'time_taken_seconds',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'total_points' => 'integer',
            'score_percentage' => 'decimal:2',
            'time_taken_seconds' => 'integer',
            'is_completed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the quiz this attempt is for.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the player who made this attempt.
     */
    public function playerProfile(): BelongsTo
    {
        return $this->belongsTo(PlayerProfile::class);
    }

    /**
     * Get all answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    /**
     * Get the rewards for this attempt.
     */
    public function reward(): HasOne
    {
        return $this->hasOne(QuizReward::class);
    }

    /**
     * Get Siblon level ups from this attempt.
     */
    public function siblonLevelUps(): HasMany
    {
        return $this->hasMany(SiblonLevelUp::class);
    }

    /**
     * Calculate and save the final score.
     */
    public function calculateScore(): void
    {
        $this->score = $this->answers()->sum('points_earned');
        $this->score_percentage = ($this->score / $this->total_points) * 100;
        $this->save();
    }

    /**
     * Mark the attempt as completed.
     */
    public function complete(): void
    {
        $this->completed_at = now();
        $this->is_completed = true;
        $this->time_taken_seconds = now()->diffInSeconds($this->started_at);
        $this->calculateScore();
        $this->save();
    }
}
