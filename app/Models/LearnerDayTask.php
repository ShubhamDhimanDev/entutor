<?php

namespace App\Models;

use Database\Factories\LearnerDayTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_program_id
 * @property int $day_task_id
 * @property Carbon|null $created_at
 * @property-read LearnerProgram $learnerProgram
 * @property-read DayTask $dayTask
 */
#[Fillable(['learner_program_id', 'day_task_id'])]
class LearnerDayTask extends Model
{
    /** @use HasFactory<LearnerDayTaskFactory> */
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
     * @return BelongsTo<DayTask, $this>
     */
    public function dayTask(): BelongsTo
    {
        return $this->belongsTo(DayTask::class);
    }
}
