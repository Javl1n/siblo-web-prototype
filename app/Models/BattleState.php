<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleState extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'battle_id',
        'player1_id',
        'player2_id',
        'player1_siblon_id',
        'player2_siblon_id',
        'current_turn',
        'turn_player_id',
        'player1_hp',
        'player2_hp',
        'battle_type',
        'status',
        'winner_id',
        'battle_log',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'battle_log' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the first player (initiator).
     */
    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    /**
     * Get the second player (opponent).
     */
    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    /**
     * Get the player whose turn it is.
     */
    public function turnPlayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'turn_player_id');
    }

    /**
     * Get the winner of the battle.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Get player 1's Siblon.
     */
    public function player1Siblon(): BelongsTo
    {
        return $this->belongsTo(PlayerSiblon::class, 'player1_siblon_id');
    }

    /**
     * Get player 2's Siblon.
     */
    public function player2Siblon(): BelongsTo
    {
        return $this->belongsTo(PlayerSiblon::class, 'player2_siblon_id');
    }

    /**
     * Check if battle is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if battle is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Add an entry to the battle log.
     */
    public function addLogEntry(array $entry): void
    {
        $log = $this->battle_log ?? [];
        $log[] = array_merge($entry, ['timestamp' => now()->toIso8601String()]);
        $this->battle_log = $log;
        $this->save();
    }
}
