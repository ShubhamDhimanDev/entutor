<?php

namespace Database\Factories;

use App\Enums\RoleplaySide;
use App\Models\RoleplayLine;
use App\Models\RoleplayScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleplayLine>
 */
class RoleplayLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'roleplay_scenario_id' => RoleplayScenario::factory(),
            'side' => fake()->randomElement(RoleplaySide::cases()),
            'speaker_label' => fake()->firstName(),
            'line_text' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 50),
        ];
    }
}
