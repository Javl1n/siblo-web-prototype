<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerProfile>
 */
class PlayerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trainer_name' => fake()->firstName().' '.fake()->randomElement(['Trainer', 'Champion', 'Master', 'Ace']),
            'level' => fake()->numberBetween(1, 20),
            'experience_points' => fake()->numberBetween(0, 10000),
            'coins' => fake()->numberBetween(0, 5000),
        ];
    }
}
