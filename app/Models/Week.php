<?php

namespace App\Models;

use Database\Factories\WeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $month_id
 * @property int $week_number
 * @property int $week_in_month
 * @property int $start_day_number
 * @property int $end_day_number
 * @property string $title
 * @property string $mastery_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Month $month
 * @property-read Collection<int, Day> $days
 */
#[Fillable([
    'month_id',
    'week_number',
    'week_in_month',
    'start_day_number',
    'end_day_number',
    'title',
    'mastery_description',
])]
class Week extends Model
{
    /** @use HasFactory<WeekFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_number' => 'integer',
            'week_in_month' => 'integer',
            'start_day_number' => 'integer',
            'end_day_number' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Month, $this>
     */
    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    /**
     * @return HasMany<Day, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(Day::class);
    }
}
