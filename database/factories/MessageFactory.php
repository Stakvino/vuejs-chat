<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => fake()->text(25, 125),
            'channel_id' => fake()->numberBetween(1, 5),
            'user_id' => fake()->numberBetween(1, 5),
            'created_at' => fake()->dateTimeThisMonth(),
        ];
    }
}
