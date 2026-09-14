<?php

namespace App\Models;

use Database\Factories\LearnerWeeklyMilestoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_program_id
 * @property int $week_id
 * @property int|null $confirmed_by_user_id
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property-read LearnerProgram $learnerProgram
 * @property-read Week $week
 * @property-read User|null $confirmedBy
 */
#[Fillable(['learner_program_id', 'week_id', 'confirmed_by_user_id', 'note'])]
class LearnerWeeklyMilestone extends Model
{
    /** @use HasFactory<LearnerWeeklyMilestoneFactory> */
    use HasFactory;

    /**
     * This table has no `updated_at` column.
     */
    const UPDATED_AT = null;

    /**
     * @return BelongsTo<LearnerProgram, $this>
     */
    public function learnerProgram(): BelongsTo
    {
        return $this->belongsTo(LearnerProgram::class);
    }

    /**
     * @return BelongsTo<Week, $this>
     */
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}
