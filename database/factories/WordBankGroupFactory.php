<?php

namespace Database\Factories;

use App\Models\WordBankGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WordBankGroup>
 */
class WordBankGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'order' => fake()->unique()->numberBetween(1, 20),
        ];
    }
}
