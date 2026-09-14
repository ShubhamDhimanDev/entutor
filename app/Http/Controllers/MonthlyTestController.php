<?php

namespace App\Http\Controllers;

use App\Actions\RecordMonthlyTest;
use App\Http\Requests\StoreMonthlyTestRecordRequest;
use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\MonthlyTestSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyTestController extends Controller
{
    /**
     * Show a month's test rubric plus this learner's existing record, if any.
     */
    public function show(Request $request, Month $month): Response
    {
        $month->loadMissing('monthlyTest.sections');

        $learnerProgram = $request->user()->learnerProgram;

        $record = $learnerProgram === null
            ? null
            : LearnerMonthlyTestRecord::query()
                ->where('learner_program_id', $learnerProgram->id)
                ->where('month_id', $month->id)
                ->with('scores')
                ->first();

        return Inertia::render('months/test', [
            'month' => [
                'id' => $month->id,
                'month_number' => $month->month_number,
                'theme' => $month->theme,
            ],
            'test' => $month->monthlyTest === null ? null : [
                'band_excellent_min' => $month->monthlyTest->band_excellent_min,
                'band_good_min' => $month->monthlyTest->band_good_min,
                'band_fair_min' => $month->monthlyTest->band_fair_min,
                'sections' => $month->monthlyTest->sections
                    ->sortBy('order')
                    ->values()
                    ->map(fn (MonthlyTestSection $section): array => [
                        'id' => $section->id,
                        'skill' => $section->skill->value,
                        'weight' => $section->weight,
                        'content' => $section->content,
                        'order' => $section->order,
                    ]),
            ],
            'record' => $record === null ? null : [
                'id' => $record->id,
                'taken_on' => $record->taken_on->toDateString(),
                'total_score' => $record->total_score,
                'band' => $record->band->value,
                'band_label' => $record->band->label(),
                'scores' => $record->scores->map(fn ($score): array => [
                    'skill' => $score->skill->value,
                    'score' => $score->score,
                ])->values(),
            ],
            'canAct' => $learnerProgram !== null,
        ]);
    }

    /**
     * Record the current learner's own monthly test result.
     */
    public function store(StoreMonthlyTestRecordRequest $request, Month $month, RecordMonthlyTest $action): RedirectResponse
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return $this->record($request, $learnerProgram, $month, $action);
    }

    /**
     * Record a monthly test result on behalf of a linked learner (coach side).
     */
    public function storeForLearner(
        StoreMonthlyTestRecordRequest $request,
        LearnerProgram $learnerProgram,
        Month $month,
        RecordMonthlyTest $action,
    ): RedirectResponse {
        return $this->record($request, $learnerProgram, $month, $action);
    }

    private function record(
        StoreMonthlyTestRecordRequest $request,
        LearnerProgram $learnerProgram,
        Month $month,
        RecordMonthlyTest $action,
    ): RedirectResponse {
        $this->authorize('createOrUpdate', [LearnerMonthlyTestRecord::class, $learnerProgram]);

        $takenOn = $request->validated('taken_on');
        $rawScores = $request->validated('scores');

        $scores = null;

        if (is_array($rawScores)) {
            $scores = [];

            foreach ($rawScores as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $scores[] = [
                    'skill' => (string) $entry['skill'],
                    'score' => (int) $entry['score'],
                ];
            }
        }

        $action->handle($learnerProgram, $month, $request->user(), [
            'taken_on' => is_string($takenOn) ? $takenOn : null,
            'total_score' => (int) $request->validated('total_score'),
            'scores' => $scores,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Monthly test recorded.')]);

        return back();
    }
}
