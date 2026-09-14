<?php

namespace App\Http\Controllers;

use App\Actions\UpsertWeeklyRating;
use App\Http\Requests\UpsertWeeklyRatingRequest;
use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyRating;
use App\Models\Week;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WeeklyRatingController extends Controller
{
    /**
     * Record or update the weekly rating for the current learner's own program.
     */
    public function store(UpsertWeeklyRatingRequest $request, Week $week, UpsertWeeklyRating $action): RedirectResponse
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return $this->upsert($request, $learnerProgram, $week, $action);
    }

    /**
     * Record or update a weekly rating on behalf of a linked learner (coach side).
     */
    public function storeForLearner(
        UpsertWeeklyRatingRequest $request,
        LearnerProgram $learnerProgram,
        Week $week,
        UpsertWeeklyRating $action,
    ): RedirectResponse {
        return $this->upsert($request, $learnerProgram, $week, $action);
    }

    private function upsert(
        UpsertWeeklyRatingRequest $request,
        LearnerProgram $learnerProgram,
        Week $week,
        UpsertWeeklyRating $action,
    ): RedirectResponse {
        $this->authorize('createOrUpdate', [LearnerWeeklyRating::class, $learnerProgram]);

        $note = (string) $request->string('note');

        $action->handle($learnerProgram, $week, $request->user(), [
            'reading_rating' => $request->integer('reading_rating'),
            'speaking_rating' => $request->integer('speaking_rating'),
            'listening_rating' => $request->integer('listening_rating'),
            'confidence_rating' => $request->integer('confidence_rating'),
            'note' => $note === '' ? null : $note,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Weekly rating saved.')]);

        return back();
    }
}
