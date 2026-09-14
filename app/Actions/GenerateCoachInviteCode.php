<?php

namespace App\Actions;

use App\Enums\CoachLinkStatus;
use App\Models\CoachLink;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GenerateCoachInviteCode
{
    /**
     * Return the learner's current invite code, generating a fresh one if needed.
     *
     * If the learner already has an active coach, this is rejected. If a
     * pending, unexpired invite code already exists it is returned as-is
     * rather than duplicated.
     *
     * @throws ValidationException if the learner already has an active coach link.
     */
    public function handle(User $learner): CoachLink
    {
        return DB::transaction(function () use ($learner): CoachLink {
            $active = CoachLink::query()
                ->where('learner_user_id', $learner->id)
                ->where('status', CoachLinkStatus::Active)
                ->first();

            if ($active !== null) {
                throw ValidationException::withMessages([
                    'coach' => 'You already have an active coach linked. Revoke the current link before generating a new invite code.',
                ]);
            }

            $pending = CoachLink::query()
                ->where('learner_user_id', $learner->id)
                ->where('status', CoachLinkStatus::Pending)
                ->where('invite_code_expires_at', '>', now())
                ->latest('id')
                ->first();

            if ($pending !== null) {
                return $pending;
            }

            // invite_code has a DB-level unique constraint, so even though the
            // pre-check below makes a collision astronomically unlikely
            // (36^8 possibilities), retry a couple of times on a genuine
            // collision instead of letting it bubble up as a 500.
            for ($attempt = 0; ; $attempt++) {
                do {
                    $code = Str::upper(Str::random(8));
                } while (CoachLink::query()->where('invite_code', $code)->exists());

                try {
                    return CoachLink::query()->create([
                        'learner_user_id' => $learner->id,
                        'invite_code' => $code,
                        'invite_code_expires_at' => now()->addDays(7),
                        'status' => CoachLinkStatus::Pending,
                    ]);
                } catch (UniqueConstraintViolationException $exception) {
                    if ($attempt >= 2) {
                        throw $exception;
                    }
                }
            }
        });
    }
}
