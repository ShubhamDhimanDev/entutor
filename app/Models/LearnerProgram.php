<?php

namespace App\Models;

use Database\Factories\LearnerProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $start_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, LearnerDayTask> $learnerDayTasks
 * @property-read Collection<int, LearnerWeeklyMilestone> $weeklyMilestones
 * @property-read Collection<int, LearnerWeeklyRating> $weeklyRatings
 * @property-read Collection<int, LearnerMonthlyTestRecord> $monthlyTestRecords
 */
#[Fillable(['user_id', 'start_date'])]
class LearnerProgram extends Model
{
    /** @use HasFactory<LearnerProgramFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether the given user may manage this program: the learner
     * themself, or the learner's currently-active linked coach.
     */
    public function isManagedBy(User $user): bool
    {
        if ($user->id === $this->user_id) {
            return true;
        }

        return $this->user->coachLinkAsLearner?->coach_user_id === $user->id;
    }

    /**
     * @return HasMany<LearnerDayTask, $this>
     */
    public function learnerDayTasks(): HasMany
    {
        return $this->hasMany(LearnerDayTask::class);
    }

    /**
     * @return HasMany<LearnerWeeklyMilestone, $this>
     */
    public function weeklyMilestones(): HasMany
    {
        return $this->hasMany(LearnerWeeklyMilestone::class);
    }

    /**
     * @return HasMany<LearnerWeeklyRating, $this>
     */
    public function weeklyRatings(): HasMany
    {
        return $this->hasMany(LearnerWeeklyRating::class);
    }

    /**
     * @return HasMany<LearnerMonthlyTestRecord, $this>
     */
    public function monthlyTestRecords(): HasMany
    {
        return $this->hasMany(LearnerMonthlyTestRecord::class);
    }
}
