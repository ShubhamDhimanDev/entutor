<?php

namespace Database\Factories;

use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerProgram;
use App\Models\Month;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerMonthlyTestRecord>
 */
class LearnerMonthlyTestRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learner_program_id' => LearnerProgram::factory(),
            'month_id' => Month::factory(),
            'taken_on' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'total_score' => fake()->numberBetween(0, 100),
            'recorded_by_user_id' => null,
        ];
    }
}
