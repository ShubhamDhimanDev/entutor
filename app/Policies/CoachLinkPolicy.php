<?php

namespace App\Policies;

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\User;

class CoachLinkPolicy
{
    /**
     * Only a learner may generate an invite code.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Learner;
    }

    /**
     * Only a coach may redeem a still-pending invite code.
     */
    public function redeem(User $user, CoachLink $coachLink): bool
    {
        return $user->role === UserRole::Coach
            && $coachLink->status === CoachLinkStatus::Pending;
    }

    /**
     * Either party to the link may revoke it.
     */
    public function revoke(User $user, CoachLink $coachLink): bool
    {
        return $user->id === $coachLink->learner_user_id
            || $user->id === $coachLink->coach_user_id;
    }
}
