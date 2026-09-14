<?php

namespace App\Http\Controllers;

use App\Actions\BuildWeekProgressList;
use App\Enums\SkillArea;
use App\Models\DayTask;
use App\Models\Month;
use App\Models\Tense;
use App\Models\Week;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthController extends Controller
{
    /**
     * Show a month's overview and its weeks, annotated with this learner's
     * milestone/rating state (curriculum content itself is global and not
     * access-gated; a non-learner viewing directly by URL just sees
     * unconfirmed/unrated weeks).
     */
    public function show(Request $request, Month $month, BuildWeekProgressList $builder): Response
    {
        $month->load(['weeks' => fn ($query) => $query->orderBy('week_number')]);

        $learnerProgram = $request->user()->learnerProgram;

        $weeks = $learnerProgram === null
            ? $month->weeks->map(fn (Week $week): array => [
                'id' => $week->id,
                'week_number' => $week->week_number,
                'week_in_month' => $week->week_in_month,
                'month_id' => $week->month_id,
                'title' => $week->title,
                'mastery_description' => $week->mastery_description,
                'start_day_number' => $week->start_day_number,
                'end_day_number' => $week->end_day_number,
                'milestone_confirmed' => false,
                'milestone_confirmed_at' => null,
                'rating' => null,
            ])->values()->all()
            : $builder->handle($learnerProgram, $month->weeks);

        return Inertia::render('months/show', [
            'month' => [
                'id' => $month->id,
                'month_number' => $month->month_number,
                'theme' => $month->theme,
                'goals' => $month->goals,
                'expected_outcomes' => $month->expected_outcomes,
                'daily_emphasis' => $month->daily_emphasis,
                'speaking_practice_ideas' => $month->speaking_practice_ideas,
                'reading_materials_ideas' => $month->reading_materials_ideas,
                'vocabulary_target' => $month->vocabulary_target,
            ],
            'weeks' => $weeks,
            'tenses' => $this->monthTenses($month),
            'canAct' => $learnerProgram !== null,
        ]);
    }

    /**
     * This month's distinct tenses taught, derived from its Grammar day
     * tasks rather than a stored pivot (there is no month<->tense table).
     *
     * @return array<int, array{id: int, key: string, name: string}>
     */
    private function monthTenses(Month $month): array
    {
        return DayTask::query()
            ->where('type', SkillArea::Grammar)
            ->whereHas('day', fn ($query) => $query->where('month_id', $month->id))
            ->with('tense')
            ->get()
            ->pluck('tense')
            ->filter()
            ->unique('id')
            ->sortBy('order')
            ->values()
            ->map(fn (Tense $tense): array => [
                'id' => $tense->id,
                'key' => $tense->key->value,
                'name' => $tense->name,
            ])
            ->all();
    }
}
