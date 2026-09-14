<?php

namespace App\Models;

use Database\Factories\MonthlyTestFactory;
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
 * @property int $band_excellent_min
 * @property int $band_good_min
 * @property int $band_fair_min
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Month $month
 * @property-read Collection<int, MonthlyTestSection> $sections
 */
#[Fillable(['month_id', 'band_excellent_min', 'band_good_min', 'band_fair_min'])]
class MonthlyTest extends Model
{
    /** @use HasFactory<MonthlyTestFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'band_excellent_min' => 'integer',
            'band_good_min' => 'integer',
            'band_fair_min' => 'integer',
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
     * @return HasMany<MonthlyTestSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(MonthlyTestSection::class);
    }
}
