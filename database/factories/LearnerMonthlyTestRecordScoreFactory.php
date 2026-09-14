<?php

namespace Database\Factories;

use App\Enums\SkillArea;
use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerMonthlyTestRecordScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerMonthlyTestRecordScore>
 */
class LearnerMonthlyTestRecordScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learner_monthly_test_record_id' => LearnerMonthlyTestRecord::factory(),
            'skill' => fake()->randomElement(SkillArea::cases()),
            'score' => fake()->numberBetween(0, 100),
        ];
    }
}
