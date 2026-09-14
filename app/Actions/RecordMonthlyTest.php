<?php

namespace App\Actions;

use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerMonthlyTestRecordScore;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordMonthlyTest
{
    /**
     * Create or update a learner's monthly test record, upserting on the
     * unique (learner_program_id, month_id) constraint. Per-skill sub-scores
     * are optional and independently upserted on their own unique
     * constraint — omitting a skill on a later submission leaves any
     * previously-recorded score for that skill untouched.
     *
     * @param  array{taken_on?: string|null, total_score: int, scores?: array<int, array{skill: string, score: int}>|null}  $data
     */
    public function handle(LearnerProgram $learnerProgram, Month $month, User $recordedBy, array $data): LearnerMonthlyTestRecord
    {
        return DB::transaction(function () use ($learnerProgram, $month, $recordedBy, $data): LearnerMonthlyTestRecord {
            $record = LearnerMonthlyTestRecord::query()->updateOrCreate(
                [
                    'learner_program_id' => $learnerProgram->id,
                    'month_id' => $month->id,
                ],
                [
                    'taken_on' => $data['taken_on'] ?? now()->toDateString(),
                    'total_score' => $data['total_score'],
                    'recorded_by_user_id' => $recordedBy->id,
                ],
            );

            foreach ($data['scores'] ?? [] as $scoreData) {
                LearnerMonthlyTestRecordScore::query()->updateOrCreate(
                    [
                        'learner_monthly_test_record_id' => $record->id,
                        'skill' => $scoreData['skill'],
                    ],
                    [
                        'score' => $scoreData['score'],
                    ],
                );
            }

            return $record->refresh()->load('scores');
        });
    }
}
