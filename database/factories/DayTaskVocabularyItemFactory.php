<?php

namespace Database\Factories;

use App\Models\DayTask;
use App\Models\DayTaskVocabularyItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayTaskVocabularyItem>
 */
class DayTaskVocabularyItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_task_id' => DayTask::factory(),
            'word_bank_entry_id' => null,
            'term' => fake()->word(),
            'native_meaning' => fake()->word(),
            'order' => fake()->numberBetween(1, 20),
        ];
    }
}
