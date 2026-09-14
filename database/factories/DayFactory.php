<?php

namespace Database\Factories;

use App\Models\Day;
use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Day>
 */
class DayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'week_id' => Week::factory(),
            'month_id' => Month::factory(),
            'day_number' => fake()->unique()->numberBetween(1, 180),
            'title' => fake()->sentence(4),
        ];
    }
}
