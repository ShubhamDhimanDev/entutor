<?php

use App\Enums\SkillArea;
use Database\Seeders\MonthlyTestSeeder;

/**
 * Exercises MonthlyTestSeeder::tests() directly (no DB, no db:seed) so the
 * "each month's section weights sum to 100" invariant — otherwise only
 * checked by a RuntimeException at seed time — is covered by the Pest suite.
 */
function monthlyTestSeederData(): array
{
    $seeder = new class extends MonthlyTestSeeder
    {
        /**
         * @return array<int, array<int, array{skill: SkillArea, weight: int, content: string}>>
         */
        public function tests(): array
        {
            return parent::tests();
        }
    };

    return $seeder->tests();
}

test('monthly test seeder defines data for all 6 months', function () {
    expect(monthlyTestSeederData())->toHaveKeys([1, 2, 3, 4, 5, 6]);
});

test('each month\'s monthly test section weights sum to exactly 100', function () {
    foreach (monthlyTestSeederData() as $monthNumber => $sections) {
        $totalWeight = array_sum(array_column($sections, 'weight'));

        expect($totalWeight)->toBe(
            100,
            "Month {$monthNumber} monthly test sections sum to {$totalWeight}, expected 100."
        );
    }
});
