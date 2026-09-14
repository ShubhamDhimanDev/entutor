<?php

namespace Database\Factories;

use App\Models\ConfidenceQuestion;
use App\Models\ConfidenceTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfidenceQuestion>
 */
class ConfidenceQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'confidence_topic_id' => ConfidenceTopic::factory(),
            'question' => fake()->sentence(),
            'example_answer' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 500),
        ];
    }
}
