<?php

namespace App\Actions;

use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyRating;
use App\Models\User;
use App\Models\Week;

class UpsertWeeklyRating
{
    /**
     * Create or update the weekly rating for a learner's program, upserting
     * on the unique (learner_program_id, week_id) constraint.
     *
     * @param  array{reading_rating: int, speaking_rating: int, listening_rating: int, confidence_rating: int, note?: string|null}  $data
     */
    public function handle(LearnerProgram $learnerProgram, Week $week, User $ratedBy, array $data): LearnerWeeklyRating
    {
        return LearnerWeeklyRating::query()->updateOrCreate(
            [
                'learner_program_id' => $learnerProgram->id,
                'week_id' => $week->id,
            ],
            [
                'reading_rating' => $data['reading_rating'],
                'speaking_rating' => $data['speaking_rating'],
                'listening_rating' => $data['listening_rating'],
                'confidence_rating' => $data['confidence_rating'],
                'note' => $data['note'] ?? null,
                'rated_by_user_id' => $ratedBy->id,
            ],
        );
    }
}
