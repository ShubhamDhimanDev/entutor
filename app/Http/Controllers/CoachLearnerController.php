<?php

namespace App\Http\Controllers;

use App\Actions\BuildLearnerDashboardData;
use App\Actions\BuildMonthTestProgressList;
use App\Actions\BuildWeekProgressList;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\Week;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoachLearnerController extends Controller
{
    /**
     * List the learners currently linked to the authenticated coach.
     */
    public function index(Request $request): Response
    {
        $coach = $request->user();

        $learners = LearnerProgram::query()
            ->whereHas(
                'user.coachLinkAsLearner',
                fn ($query) => $query->where('coach_user_id', $coach->id),
            )
            ->with('user')
            ->orderBy('id')
            ->get()
            ->map(fn (LearnerProgram $program) => [
                'id' => $program->id,
                'name' => $program->user->name,
                'email' => $program->user->email,
                'start_date' => $program->start_date->toDateString(),
            ]);

        return Inertia::render('coach/learners/index', [
            'learners' => $learners,
        ]);
    }

    /**
     * Show a linked learner's read-only progress: the same dashboard-shape
     * rollup the learner sees themself, plus the full weeks/months lists the
     * coach can act on (milestone confirm, rating entry, test-record entry —
     * day-task completion stays learner-only per LearnerDayTaskPolicy).
     */
    public function show(
        LearnerProgram $learnerProgram,
        BuildLearnerDashboardData $dashboardBuilder,
        BuildWeekProgressList $weekProgressBuilder,
        BuildMonthTestProgressList $monthTestProgressBuilder,
    ): Response {
        $this->authorize('view', $learnerProgram);

        $learnerProgram->loadMissing('user');

        $weeks = Week::query()->orderBy('week_number')->get();
        $months = Month::query()->orderBy('month_number')->get();

        return Inertia::render('coach/learners/show', [
            'learner' => [
                'id' => $learnerProgram->id,
                'name' => $learnerProgram->user->name,
                'email' => $learnerProgram->user->email,
                'start_date' => $learnerProgram->start_date->toDateString(),
            ],
            'dashboard' => $dashboardBuilder->handle($learnerProgram),
            'weeks' => $weekProgressBuilder->handle($learnerProgram, $weeks),
            'months' => $monthTestProgressBuilder->handle($learnerProgram, $months),
        ]);
    }
}
