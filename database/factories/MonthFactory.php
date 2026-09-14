<?php

namespace Database\Factories;

use App\Models\Month;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Month>
 */
class MonthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month_number' => fake()->unique()->numberBetween(1, 6),
            'theme' => fake()->sentence(3),
            'goals' => fake()->sentences(3),
            'expected_outcomes' => fake()->sentences(3),
            'daily_emphasis' => fake()->paragraph(),
            'speaking_practice_ideas' => fake()->sentences(3),
            'reading_materials_ideas' => fake()->sentences(3),
            'vocabulary_target' => fake()->sentence(),
        ];
    }
}
