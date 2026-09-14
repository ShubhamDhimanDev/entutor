<?php

namespace Database\Factories;

use App\Models\ConfidenceTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfidenceTopic>
 */
class ConfidenceTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'order' => fake()->unique()->numberBetween(1, 50),
        ];
    }
}
