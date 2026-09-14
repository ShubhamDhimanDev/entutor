<?php

namespace App\Actions;

use App\Models\Day;
use App\Models\LearnerProgram;
use Illuminate\Support\Facades\DB;

class ComputeLearnerDailyProgress
{
    /**
     * Compute the day-level rollup for a learner's program in a fixed number
     * of cheap queries (no N+1 per day), regardless of how many days have
     * been seeded so far.
     *
     * A day counts as done when every one of its sibling DayTask rows has a
     * matching LearnerDayTask for this program. The "current day" is the
     * lowest day_number that is not yet fully done; if every seeded day is
     * done, the learner is fully caught up and currentDayNumber is null
     * rather than an error.
     *
     * @return array{daysDone: int, totalDays: int, currentDayNumber: int|null, incompleteDayNumbers: array<int, int>}
     */
    public function handle(LearnerProgram $learnerProgram): array
    {
        $totalDays = Day::query()->count();

        /** @var array<int, int> $incompleteDayNumbers */
        $incompleteDayNumbers = DB::table('days')
            ->select('days.day_number')
            ->join('day_tasks', 'day_tasks.day_id', '=', 'days.id')
            ->leftJoin('learner_day_tasks', function ($join) use ($learnerProgram) {
                $join->on('learner_day_tasks.day_task_id', '=', 'day_tasks.id')
                    ->where('learner_day_tasks.learner_program_id', '=', $learnerProgram->id);
            })
            ->groupBy('days.id', 'days.day_number')
            ->havingRaw('count(day_tasks.id) > count(learner_day_tasks.id)')
            ->orderBy('days.day_number')
            ->pluck('days.day_number')
            ->map(fn (mixed $dayNumber): int => (int) $dayNumber)
            ->values()
            ->all();

        return [
            'daysDone' => $totalDays - count($incompleteDayNumbers),
            'totalDays' => $totalDays,
            'currentDayNumber' => $incompleteDayNumbers[0] ?? null,
            'incompleteDayNumbers' => $incompleteDayNumbers,
        ];
    }
}
