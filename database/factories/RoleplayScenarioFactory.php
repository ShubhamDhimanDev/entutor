<?php

namespace Database\Factories;

use App\Enums\RoleplayDomain;
use App\Models\RoleplayScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleplayScenario>
 */
class RoleplayScenarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'domain' => fake()->randomElement(RoleplayDomain::cases()),
            'tip' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 500),
        ];
    }
}
