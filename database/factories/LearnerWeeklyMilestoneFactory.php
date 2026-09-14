<?php

namespace Database\Factories;

use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyMilestone;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerWeeklyMilestone>
 */
class LearnerWeeklyMilestoneFactory extends Factory
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
            'week_id' => Week::factory(),
            'confirmed_by_user_id' => null,
            'note' => null,
        ];
    }
}
