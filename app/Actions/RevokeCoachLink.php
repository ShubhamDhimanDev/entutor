<?php

namespace App\Actions;

use App\Enums\CoachLinkStatus;
use App\Models\CoachLink;
use App\Models\User;

class RevokeCoachLink
{
    /**
     * Revoke a coach link. The row is kept (not deleted) as an audit trail.
     */
    public function handle(CoachLink $coachLink, User $revokedBy): CoachLink
    {
        $coachLink->forceFill([
            'status' => CoachLinkStatus::Revoked,
            'revoked_at' => now(),
            'revoked_by_user_id' => $revokedBy->id,
        ])->save();

        return $coachLink;
    }
}
