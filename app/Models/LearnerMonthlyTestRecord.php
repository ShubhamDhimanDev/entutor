<?php

namespace App\Models;

use App\Enums\TestScoreBand;
use Database\Factories\LearnerMonthlyTestRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_program_id
 * @property int $month_id
 * @property Carbon $taken_on
 * @property int $total_score
 * @property int|null $recorded_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LearnerProgram $learnerProgram
 * @property-read Month $month
 * @property-read User|null $recordedBy
 * @property-read Collection<int, LearnerMonthlyTestRecordScore> $scores
 * @property-read TestScoreBand $band
 */
#[Fillable(['learner_program_id', 'month_id', 'taken_on', 'total_score', 'recorded_by_user_id'])]
class LearnerMonthlyTestRecord extends Model
{
    /** @use HasFactory<LearnerMonthlyTestRecordFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'total_score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<LearnerProgram, $this>
     */
    public function learnerProgram(): BelongsTo
    {
        return $this->belongsTo(LearnerProgram::class);
    }

    /**
     * @return BelongsTo<Month, $this>
     */
    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * @return HasMany<LearnerMonthlyTestRecordScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(LearnerMonthlyTestRecordScore::class);
    }

    /**
     * The scoring band (Excellent/Good/Fair/Needs work) for this record's
     * total score, derived on read from the month's test bands rather than
     * stored. Lazy-loads `month.monthlyTest` if not already eager-loaded —
     * callers iterating many records should eager-load `month.monthlyTest`
     * first to avoid N+1s.
     *
     * @return Attribute<TestScoreBand, never>
     */
    protected function band(): Attribute
    {
        return Attribute::get(
            fn (): TestScoreBand => TestScoreBand::forScore($this->total_score, $this->month->monthlyTest)
        );
    }
}
