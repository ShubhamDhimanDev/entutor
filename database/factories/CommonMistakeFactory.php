<?php

namespace Database\Factories;

use App\Enums\MistakeTag;
use App\Models\CommonMistake;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommonMistake>
 */
class CommonMistakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wrong_sentence' => fake()->sentence(),
            'corrected_sentence' => fake()->sentence(),
            'explanation' => fake()->paragraph(),
            'tag' => fake()->randomElement(MistakeTag::cases()),
            'order' => fake()->numberBetween(1, 500),
        ];
    }
}
