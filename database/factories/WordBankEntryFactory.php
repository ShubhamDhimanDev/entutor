<?php

namespace Database\Factories;

use App\Models\WordBankEntry;
use App\Models\WordBankGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WordBankEntry>
 */
class WordBankEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word_bank_group_id' => WordBankGroup::factory(),
            'term' => fake()->word(),
            'native_language' => 'hi',
            'native_meaning' => fake()->word(),
            'native_transliteration' => fake()->word(),
            'example_sentence' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 500),
        ];
    }
}
