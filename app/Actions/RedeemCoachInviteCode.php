<?php

namespace App\Actions;

use App\Enums\CoachLinkStatus;
use App\Models\CoachLink;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RedeemCoachInviteCode
{
    /**
     * Redeem a pending, unexpired invite code on behalf of a coach.
     *
     * Re-checks (inside the transaction) that the learner still has no active
     * coach, as a belt-and-suspenders guard alongside the DB-level partial
     * unique index against a race between two simultaneous redemptions of
     * *different* invite codes for the same learner.
     *
     * That index does not, by itself, protect against two concurrent
     * redemptions of the *same* invite code (e.g. a double-submit, or two
     * coaches racing on a leaked code): both requests could read the row
     * while it is still 'pending' before either writes. The final write
     * below is therefore a conditional update (`WHERE status = pending`)
     * rather than a plain save, so only the first writer to actually commit
     * wins the row; a loser affects zero rows and is treated the same as an
     * invalid/expired code instead of silently overwriting the winner's
     * `coach_user_id`.
     *
     * @throws ValidationException if the code is invalid/expired, or the learner already has an active coach.
     */
    public function handle(User $coach, string $code): CoachLink
    {
        return DB::transaction(function () use ($coach, $code): CoachLink {
            $coachLink = CoachLink::query()
                ->where('invite_code', $code)
                ->where('status', CoachLinkStatus::Pending)
                ->where('invite_code_expires_at', '>', now())
                ->first();

            if ($coachLink === null) {
                throw ValidationException::withMessages([
                    'code' => 'That invite code is invalid or has expired.',
                ]);
            }

            $learnerAlreadyLinked = CoachLink::query()
                ->where('learner_user_id', $coachLink->learner_user_id)
                ->where('status', CoachLinkStatus::Active)
                ->exists();

            if ($learnerAlreadyLinked) {
                throw ValidationException::withMessages([
                    'code' => 'This learner already has an active coach linked.',
                ]);
            }

            $updated = CoachLink::query()
                ->whereKey($coachLink->id)
                ->where('status', CoachLinkStatus::Pending)
                ->update([
                    'coach_user_id' => $coach->id,
                    'status' => CoachLinkStatus::Active,
                    'redeemed_at' => now(),
                ]);

            if ($updated === 0) {
                throw ValidationException::withMessages([
                    'code' => 'That invite code is invalid or has expired.',
                ]);
            }

            return $coachLink->refresh();
        });
    }
}
