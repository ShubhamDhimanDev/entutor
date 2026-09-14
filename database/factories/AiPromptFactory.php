<?php

namespace Database\Factories;

use App\Models\AiPrompt;
use App\Models\AiPromptCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiPrompt>
 */
class AiPromptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_prompt_category_id' => AiPromptCategory::factory(),
            'prompt_text' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 500),
        ];
    }
}
