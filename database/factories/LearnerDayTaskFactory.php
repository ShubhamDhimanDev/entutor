<?php

namespace Database\Factories;

use App\Models\DayTask;
use App\Models\LearnerDayTask;
use App\Models\LearnerProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerDayTask>
 */
class LearnerDayTaskFactory extends Factory
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
            'day_task_id' => DayTask::factory(),
        ];
    }
}
