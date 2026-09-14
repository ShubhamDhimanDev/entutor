<?php

namespace Database\Factories;

use App\Enums\TenseAspect;
use App\Enums\TenseKey;
use App\Enums\TenseTime;
use App\Models\Tense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tense>
 */
class TenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = fake()->unique()->randomElement(TenseKey::cases());

        return [
            'key' => $key,
            'name' => fake()->words(3, true),
            'time' => fake()->randomElement(TenseTime::cases()),
            'aspect' => fake()->randomElement(TenseAspect::cases()),
            'summary' => fake()->sentence(),
            'structure_affirmative' => 'Subject + verb',
            'structure_negative' => 'Subject + do not + verb',
            'structure_interrogative' => 'Do + subject + verb?',
            'usage_rules' => [fake()->sentence()],
            'signal_words' => [fake()->word()],
            'examples' => [
                ['sentence' => fake()->sentence(), 'native_meaning' => fake()->sentence()],
            ],
            'common_confusions' => null,
            'order' => fake()->unique()->numberBetween(1, 12),
        ];
    }
}
