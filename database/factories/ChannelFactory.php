<?php

namespace Database\Factories;

use App\Models\ChannelType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $privateId = 2;
        return [
            // 'channel_type_id' => fake()->numberBetween(1, 2),
            'channel_type_id' => $privateId,
            'created_at' => now(),
        ];
    }
}
