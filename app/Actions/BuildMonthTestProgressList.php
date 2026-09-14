<?php

namespace App\Actions;

use App\Enums\TestScoreBand;
use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerProgram;
use App\Models\Month;
use Illuminate\Database\Eloquent\Collection;

class BuildMonthTestProgressList
{
    /**
     * Annotate a set of months with this learner's monthly-test-record
     * state, in two grouped queries regardless of how many months are
     * passed in.
     *
     * @param  Collection<int, Month>  $months
     * @return array<int, array<string, mixed>>
     */
    public function handle(LearnerProgram $learnerProgram, Collection $months): array
    {
        $months->loadMissing('monthlyTest');

        $monthIds = $months->pluck('id');

        $records = LearnerMonthlyTestRecord::query()
            ->where('learner_program_id', $learnerProgram->id)
            ->whereIn('month_id', $monthIds)
            ->get()
            ->keyBy('month_id');

        return $months
            ->map(function (Month $month) use ($records): array {
                /** @var LearnerMonthlyTestRecord|null $record */
                $record = $records->get($month->id);

                return [
                    'id' => $month->id,
                    'month_number' => $month->month_number,
                    'theme' => $month->theme,
                    'record' => $record === null ? null : [
                        'taken_on' => $record->taken_on->toDateString(),
                        'total_score' => $record->total_score,
                        'band' => TestScoreBand::forScore($record->total_score, $month->monthlyTest)->value,
                        'band_label' => TestScoreBand::forScore($record->total_score, $month->monthlyTest)->label(),
                    ],
                ];
            })
            ->values()
            ->all();
    }
}
