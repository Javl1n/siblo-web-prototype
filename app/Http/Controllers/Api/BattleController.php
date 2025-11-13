<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BattleState;
use App\Models\PlayerSiblon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BattleController extends Controller
{
    /**
     * Start a new battle.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'player_siblon_id' => ['required', 'exists:player_siblons,id'],
            'battle_type' => ['required', 'in:pvp,pve,training'],
            'opponent_id' => ['nullable', 'required_if:battle_type,pvp', 'exists:users,id'],
        ]);

        $player = $request->user();
        $playerSiblon = PlayerSiblon::findOrFail($request->player_siblon_id);

        // Ensure the Siblon belongs to the player
        if ($playerSiblon->player_id !== $player->id) {
            return response()->json([
                'message' => 'This Siblon does not belong to you.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // For prototype, we'll implement training battles (vs AI)
            // PvP battles would require more complex matchmaking logic
            $opponentSiblon = null;
            $opponentId = null;

            if ($request->battle_type === 'training' || $request->battle_type === 'pve') {
                // Generate a random AI opponent from available Siblon species
                $opponentSiblon = $this->generateAIOpponent($playerSiblon->level);
            }

            // Create battle state
            $battle = BattleState::create([
                'battle_id' => Str::uuid()->toString(),
                'player1_id' => $player->id,
                'player2_id' => $opponentId,
                'player1_siblon_id' => $playerSiblon->id,
                'player2_siblon_id' => $opponentSiblon?->id,
                'current_turn' => 1,
                'turn_player_id' => $player->id,
                'player1_hp' => $playerSiblon->current_hp,
                'player2_hp' => $opponentSiblon?->current_hp ?? 50,
                'battle_type' => $request->battle_type,
                'status' => 'active',
                'battle_log' => [],
                'started_at' => now(),
            ]);

            $battle->addLogEntry([
                'action' => 'battle_start',
                'player_id' => $player->id,
                'message' => "{$player->username} started a {$request->battle_type} battle!",
            ]);

            DB::commit();

            return response()->json([
                'battle_id' => $battle->battle_id,
                'player1' => [
                    'user_id' => $player->id,
                    'username' => $player->username,
                    'siblon_id' => $playerSiblon->id,
                    'siblon_name' => $playerSiblon->nickname ?? $playerSiblon->species->name,
                    'hp' => $battle->player1_hp,
                    'max_hp' => $playerSiblon->max_hp,
                    'level' => $playerSiblon->level,
                ],
                'player2' => [
                    'user_id' => $opponentId,
                    'username' => 'AI Trainer',
                    'siblon_id' => $opponentSiblon?->id,
                    'siblon_name' => $opponentSiblon?->species->name ?? 'Wild Siblon',
                    'hp' => $battle->player2_hp,
                    'max_hp' => $opponentSiblon?->max_hp ?? 50,
                    'level' => $opponentSiblon?->level ?? max(1, $playerSiblon->level - 2),
                ],
                'current_turn' => $battle->current_turn,
                'turn_player_id' => $battle->turn_player_id,
                'status' => $battle->status,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to start battle. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current battle state.
     */
    public function show(Request $request, string $battleId): JsonResponse
    {
        $battle = BattleState::where('battle_id', $battleId)->firstOrFail();

        // Ensure the player is part of this battle
        if ($battle->player1_id !== $request->user()->id && $battle->player2_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not part of this battle.',
            ], 403);
        }

        $player1Siblon = $battle->player1Siblon;
        $player2Siblon = $battle->player2Siblon;

        return response()->json([
            'battle_id' => $battle->battle_id,
            'player1' => [
                'user_id' => $battle->player1_id,
                'username' => $battle->player1->username,
                'siblon_id' => $player1Siblon->id,
                'siblon_name' => $player1Siblon->nickname ?? $player1Siblon->species->name,
                'hp' => $battle->player1_hp,
                'max_hp' => $player1Siblon->max_hp,
                'level' => $player1Siblon->level,
            ],
            'player2' => [
                'user_id' => $battle->player2_id,
                'username' => $battle->player2?->username ?? 'AI Trainer',
                'siblon_id' => $player2Siblon?->id,
                'siblon_name' => $player2Siblon?->species->name ?? 'Wild Siblon',
                'hp' => $battle->player2_hp,
                'max_hp' => $player2Siblon?->max_hp ?? 50,
                'level' => $player2Siblon?->level ?? 1,
            ],
            'current_turn' => $battle->current_turn,
            'turn_player_id' => $battle->turn_player_id,
            'status' => $battle->status,
            'winner_id' => $battle->winner_id,
            'started_at' => $battle->started_at->toIso8601String(),
            'completed_at' => $battle->completed_at?->toIso8601String(),
            'battle_log' => $battle->battle_log,
        ], 200);
    }

    /**
     * Forfeit an active battle.
     */
    public function forfeit(Request $request, string $battleId): JsonResponse
    {
        $battle = BattleState::where('battle_id', $battleId)->firstOrFail();
        $player = $request->user();

        // Ensure the player is part of this battle
        if ($battle->player1_id !== $player->id && $battle->player2_id !== $player->id) {
            return response()->json([
                'message' => 'You are not part of this battle.',
            ], 403);
        }

        // Ensure battle is still active
        if (! $battle->isActive()) {
            return response()->json([
                'message' => 'This battle is not active.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Determine winner (the opponent)
            $winnerId = $battle->player1_id === $player->id
                ? $battle->player2_id
                : $battle->player1_id;

            $battle->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'completed_at' => now(),
            ]);

            $battle->addLogEntry([
                'action' => 'forfeit',
                'player_id' => $player->id,
                'message' => "{$player->username} forfeited the battle.",
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Battle forfeited successfully.',
                'battle_id' => $battle->battle_id,
                'winner_id' => $winnerId,
                'status' => $battle->status,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to forfeit battle. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate an AI opponent Siblon for training battles.
     */
    protected function generateAIOpponent(int $playerLevel): ?PlayerSiblon
    {
        // For the prototype, this is a simplified implementation
        // In production, this would create a temporary AI Siblon
        // or match against actual Siblon species data

        // For now, we'll return null and handle AI battles
        // without creating actual PlayerSiblon records
        return null;
    }
}
