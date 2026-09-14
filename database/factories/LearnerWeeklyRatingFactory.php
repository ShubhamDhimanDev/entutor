<?php

namespace Database\Factories;

use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyRating;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerWeeklyRating>
 */
class LearnerWeeklyRatingFactory extends Factory
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
            'reading_rating' => fake()->numberBetween(1, 5),
            'speaking_rating' => fake()->numberBetween(1, 5),
            'listening_rating' => fake()->numberBetween(1, 5),
            'confidence_rating' => fake()->numberBetween(1, 5),
            'note' => null,
            'rated_by_user_id' => null,
        ];
    }
}
