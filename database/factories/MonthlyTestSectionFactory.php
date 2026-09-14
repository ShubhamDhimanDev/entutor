<?php

namespace Database\Factories;

use App\Enums\SkillArea;
use App\Models\MonthlyTest;
use App\Models\MonthlyTestSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlyTestSection>
 */
class MonthlyTestSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'monthly_test_id' => MonthlyTest::factory(),
            'skill' => fake()->randomElement(SkillArea::cases()),
            'weight' => fake()->numberBetween(10, 40),
            'content' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 5),
        ];
    }
}
