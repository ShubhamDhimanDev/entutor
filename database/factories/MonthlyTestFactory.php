<?php

namespace Database\Factories;

use App\Models\Month;
use App\Models\MonthlyTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlyTest>
 */
class MonthlyTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month_id' => Month::factory(),
            'band_excellent_min' => 80,
            'band_good_min' => 60,
            'band_fair_min' => 40,
        ];
    }
}
