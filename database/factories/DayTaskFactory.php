<?php

namespace Database\Factories;

use App\Enums\SkillArea;
use App\Models\Day;
use App\Models\DayTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayTask>
 */
class DayTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_id' => Day::factory(),
            'type' => fake()->randomElement(SkillArea::cases()),
            'content' => fake()->paragraph(),
            'estimated_minutes' => fake()->numberBetween(5, 60),
        ];
    }
}
