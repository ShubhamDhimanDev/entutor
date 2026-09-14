<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Content seeders, in dependency order: curriculum (months/weeks/days/day
        // tasks) before anything that references it, word bank before day-task
        // vocabulary linkage. These are stubs until the english-tutor agent
        // authors real content.
        $this->call([
            MonthSeeder::class,
            Month1DaySeeder::class,
            Month2DaySeeder::class,
            Month3DaySeeder::class,
            Month4DaySeeder::class,
            Month5DaySeeder::class,
            Month6DaySeeder::class,
            WordBankSeeder::class,
            CommonMistakeSeeder::class,
            RoleplaySeeder::class,
            AiPromptSeeder::class,
            ConfidenceQaSeeder::class,
            MonthlyTestSeeder::class,
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
