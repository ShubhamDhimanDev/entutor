<?php

namespace App\Policies;

use App\Models\LearnerProgram;
use App\Models\User;

class LearnerDayTaskPolicy
{
    /**
     * Only the learner themself may toggle their own daily-task completion.
     * A coach — even one actively linked and otherwise able to manage this
     * program — must never be able to do this on the learner's behalf.
     */
    public function toggle(User $user, LearnerProgram $learnerProgram): bool
    {
        return $user->id === $learnerProgram->user_id;
    }
}
