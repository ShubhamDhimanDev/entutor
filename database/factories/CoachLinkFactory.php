<?php

namespace Database\Factories;

use App\Enums\CoachLinkStatus;
use App\Models\CoachLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachLink>
 */
class CoachLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learner_user_id' => User::factory(),
            'coach_user_id' => null,
            'invite_code' => fake()->unique()->regexify('[A-Z0-9]{8}'),
            'invite_code_expires_at' => now()->addDays(7),
            'status' => CoachLinkStatus::Pending,
            'redeemed_at' => null,
            'revoked_at' => null,
            'revoked_by_user_id' => null,
        ];
    }
}
