<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_profile_id',
        'activity_date',
        'quizzes_completed',
        'total_experience_earned',
        'total_coins_earned',
        'siblons_caught',
        'time_played_minutes',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'quizzes_completed' => 'integer',
            'total_experience_earned' => 'integer',
            'total_coins_earned' => 'integer',
            'siblons_caught' => 'integer',
            'time_played_minutes' => 'integer',
        ];
    }

    /**
     * Get the player profile this activity belongs to.
     */
    public function playerProfile(): BelongsTo
    {
        return $this->belongsTo(PlayerProfile::class);
    }
}
