<?php

namespace App\Policies;

use App\Models\LearnerProgram;
use App\Models\User;

class WeeklyRatingPolicy
{
    /**
     * The learner themself or their currently-active linked coach may record
     * or update a weekly rating.
     */
    public function createOrUpdate(User $user, LearnerProgram $learnerProgram): bool
    {
        return $learnerProgram->isManagedBy($user);
    }
}
