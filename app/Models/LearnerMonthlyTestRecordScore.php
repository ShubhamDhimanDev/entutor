<?php

namespace App\Models;

use App\Enums\SkillArea;
use Database\Factories\LearnerMonthlyTestRecordScoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_monthly_test_record_id
 * @property SkillArea $skill
 * @property int $score
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LearnerMonthlyTestRecord $learnerMonthlyTestRecord
 */
#[Fillable(['learner_monthly_test_record_id', 'skill', 'score'])]
class LearnerMonthlyTestRecordScore extends Model
{
    /** @use HasFactory<LearnerMonthlyTestRecordScoreFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skill' => SkillArea::class,
            'score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<LearnerMonthlyTestRecord, $this>
     */
    public function learnerMonthlyTestRecord(): BelongsTo
    {
        return $this->belongsTo(LearnerMonthlyTestRecord::class);
    }
}
