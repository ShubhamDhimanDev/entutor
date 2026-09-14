<?php

namespace App\Policies;

use App\Models\LearnerProgram;
use App\Models\User;

class WeeklyMilestonePolicy
{
    /**
     * The learner themself or their currently-active linked coach may confirm
     * a weekly milestone.
     */
    public function confirm(User $user, LearnerProgram $learnerProgram): bool
    {
        return $learnerProgram->isManagedBy($user);
    }
}
