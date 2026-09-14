<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmWeeklyMilestone;
use App\Http\Requests\ConfirmWeeklyMilestoneRequest;
use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyMilestone;
use App\Models\Week;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WeeklyMilestoneController extends Controller
{
    /**
     * Confirm a weekly milestone for the current learner's own program.
     */
    public function store(ConfirmWeeklyMilestoneRequest $request, Week $week, ConfirmWeeklyMilestone $action): RedirectResponse
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return $this->confirm($request, $learnerProgram, $week, $action);
    }

    /**
     * Confirm a weekly milestone on behalf of a linked learner (coach side).
     */
    public function storeForLearner(
        ConfirmWeeklyMilestoneRequest $request,
        LearnerProgram $learnerProgram,
        Week $week,
        ConfirmWeeklyMilestone $action,
    ): RedirectResponse {
        return $this->confirm($request, $learnerProgram, $week, $action);
    }

    private function confirm(
        ConfirmWeeklyMilestoneRequest $request,
        LearnerProgram $learnerProgram,
        Week $week,
        ConfirmWeeklyMilestone $action,
    ): RedirectResponse {
        $this->authorize('confirm', [LearnerWeeklyMilestone::class, $learnerProgram]);

        $action->handle($learnerProgram, $week, $request->user(), $request->validated('note'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Weekly milestone confirmed.')]);

        return back();
    }
}
