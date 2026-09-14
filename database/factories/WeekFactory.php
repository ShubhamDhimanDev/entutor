<?php

namespace Database\Factories;

use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Week>
 */
class WeekFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDay = fake()->numberBetween(1, 174);

        return [
            'month_id' => Month::factory(),
            'week_number' => fake()->unique()->numberBetween(1, 24),
            'week_in_month' => fake()->numberBetween(1, 4),
            'start_day_number' => $startDay,
            'end_day_number' => $startDay + 6,
            'title' => fake()->sentence(4),
            'mastery_description' => fake()->paragraph(),
        ];
    }
}
