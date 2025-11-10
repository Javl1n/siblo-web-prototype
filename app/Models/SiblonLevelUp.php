<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiblonLevelUp extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'player_siblon_id',
        'quiz_attempt_id',
        'old_level',
        'new_level',
        'experience_gained',
        'hp_increased',
        'attack_increased',
        'defense_increased',
        'leveled_up_at',
    ];

    protected function casts(): array
    {
        return [
            'old_level' => 'integer',
            'new_level' => 'integer',
            'experience_gained' => 'integer',
            'hp_increased' => 'integer',
            'attack_increased' => 'integer',
            'defense_increased' => 'integer',
            'leveled_up_at' => 'datetime',
        ];
    }

    /**
     * Get the Siblon that leveled up.
     */
    public function playerSiblon(): BelongsTo
    {
        return $this->belongsTo(PlayerSiblon::class);
    }

    /**
     * Get the quiz attempt that caused this level up.
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }
}
