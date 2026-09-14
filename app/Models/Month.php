<?php

namespace App\Models;

use Database\Factories\MonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $month_number
 * @property string $theme
 * @property array<int, string> $goals
 * @property array<int, string> $expected_outcomes
 * @property string $daily_emphasis
 * @property array<int, string> $speaking_practice_ideas
 * @property array<int, string> $reading_materials_ideas
 * @property string $vocabulary_target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Week> $weeks
 * @property-read Collection<int, Day> $days
 * @property-read MonthlyTest|null $monthlyTest
 */
#[Fillable([
    'month_number',
    'theme',
    'goals',
    'expected_outcomes',
    'daily_emphasis',
    'speaking_practice_ideas',
    'reading_materials_ideas',
    'vocabulary_target',
])]
class Month extends Model
{
    /** @use HasFactory<MonthFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month_number' => 'integer',
            'goals' => 'array',
            'expected_outcomes' => 'array',
            'speaking_practice_ideas' => 'array',
            'reading_materials_ideas' => 'array',
        ];
    }

    /**
     * @return HasMany<Week, $this>
     */
    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class);
    }

    /**
     * @return HasMany<Day, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(Day::class);
    }

    /**
     * @return HasOne<MonthlyTest, $this>
     */
    public function monthlyTest(): HasOne
    {
        return $this->hasOne(MonthlyTest::class);
    }
}
