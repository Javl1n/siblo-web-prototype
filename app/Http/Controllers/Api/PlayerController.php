<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Get current player's profile and stats.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $player = $user->playerProfile;

        if (! $player) {
            return response()->json([
                'message' => 'Player profile not found.',
            ], 404);
        }

        return response()->json([
            'id' => $player->id,
            'user_id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'trainer_name' => $player->trainer_name,
            'level' => $player->level,
            'experience_points' => $player->experience_points,
            'coins' => $player->coins,
            'current_region_id' => $player->current_region_id,
        ], 200);
    }

    /**
     * Get player's Siblon collection.
     */
    public function siblons(Request $request): JsonResponse
    {
        $user = $request->user();

        $siblons = $user->playerSiblons()
            ->with('species')
            ->get()
            ->map(function ($siblon) {
                return [
                    'id' => $siblon->id,
                    'species_id' => $siblon->species_id,
                    'species_name' => $siblon->species->name,
                    'nickname' => $siblon->nickname,
                    'level' => $siblon->level,
                    'experience_points' => $siblon->experience_points,
                    'current_hp' => $siblon->current_hp,
                    'max_hp' => $siblon->max_hp,
                    'attack_stat' => $siblon->attack_stat,
                    'defense_stat' => $siblon->defense_stat,
                    'speed_stat' => $siblon->speed_stat,
                    'is_in_party' => $siblon->is_in_party,
                    'caught_at' => $siblon->caught_at->toIso8601String(),
                    'species_data' => [
                        'dex_number' => $siblon->species->dex_number,
                        'type_primary' => $siblon->species->type_primary,
                        'type_secondary' => $siblon->species->type_secondary,
                        'rarity' => $siblon->species->rarity,
                        'sprite_url' => $siblon->species->sprite_url,
                        'description' => $siblon->species->description,
                    ],
                ];
            });

        // Separate party and collection
        $party = $siblons->where('is_in_party', true)->values();
        $collection = $siblons->values();

        return response()->json([
            'party' => $party,
            'collection' => $collection,
            'total_count' => $siblons->count(),
        ], 200);
    }

    /**
     * Get today's activity summary.
     */
    public function dailyActivity(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $activity = DailyActivity::where('player_id', $user->id)
            ->where('activity_date', $today)
            ->first();

        if (! $activity) {
            // Return empty activity if no record exists for today
            return response()->json([
                'activity_date' => $today,
                'quizzes_completed' => 0,
                'experience_gained' => 0,
                'battles_won' => 0,
                'battles_lost' => 0,
                'login_streak' => 0,
            ], 200);
        }

        return response()->json([
            'activity_date' => $activity->activity_date,
            'quizzes_completed' => $activity->quizzes_completed,
            'experience_gained' => $activity->experience_gained,
            'battles_won' => $activity->battles_won,
            'battles_lost' => $activity->battles_lost,
            'login_streak' => $activity->login_streak,
        ], 200);
    }
}
