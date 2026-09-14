<?php

namespace App\Policies;

use App\Models\LearnerProgram;
use App\Models\User;

class LearnerProgramPolicy
{
    /**
     * The learner themself or their currently-active linked coach may view the program.
     */
    public function view(User $user, LearnerProgram $learnerProgram): bool
    {
        return $learnerProgram->isManagedBy($user);
    }

    /**
     * Only the learner may update their own program (e.g. a future start-date edit).
     */
    public function update(User $user, LearnerProgram $learnerProgram): bool
    {
        return $user->id === $learnerProgram->user_id;
    }
}
