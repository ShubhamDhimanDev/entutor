<?php

namespace Database\Factories;

use App\Enums\SkillArea;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\Tense;
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
            // Grammar is excluded from the default random pick because it's
            // the only SkillArea that requires a non-null tense_id — use the
            // grammar() state below to get a Grammar task with its Tense.
            'type' => fake()->randomElement(
                array_filter(SkillArea::cases(), fn (SkillArea $case): bool => $case !== SkillArea::Grammar)
            ),
            'tense_id' => null,
            'content' => fake()->paragraph(),
            'estimated_minutes' => fake()->numberBetween(5, 60),
        ];
    }

    /**
     * State for a Grammar-type day task, backed by a Tense.
     */
    public function grammar(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SkillArea::Grammar,
            'tense_id' => Tense::factory(),
        ]);
    }
}
