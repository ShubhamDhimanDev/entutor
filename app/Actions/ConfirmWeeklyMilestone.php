<?php

namespace App\Actions;

use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyMilestone;
use App\Models\User;
use App\Models\Week;

class ConfirmWeeklyMilestone
{
    /**
     * Confirm a weekly milestone for a learner's program.
     *
     * Idempotent on the unique (learner_program_id, week_id) constraint: if
     * the milestone is already confirmed, the existing row is returned
     * unchanged rather than re-confirmed by a different user.
     */
    public function handle(
        LearnerProgram $learnerProgram,
        Week $week,
        User $confirmedBy,
        ?string $note = null,
    ): LearnerWeeklyMilestone {
        return LearnerWeeklyMilestone::query()->firstOrCreate(
            [
                'learner_program_id' => $learnerProgram->id,
                'week_id' => $week->id,
            ],
            [
                'confirmed_by_user_id' => $confirmedBy->id,
                'note' => $note,
            ],
        );
    }
}
