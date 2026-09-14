<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\DayTask;
use App\Models\DayTaskVocabularyItem;
use App\Models\LearnerDayTask;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DayController extends Controller
{
    /**
     * Show a single day's tasks with this learner's per-task completion
     * state (empty/all-incomplete for a non-learner, e.g. a coach browsing
     * the curriculum directly by URL — curriculum content itself is global
     * and not access-gated).
     */
    public function show(Request $request, Day $day): Response
    {
        $day->load([
            'week',
            'month',
            // Order by id (creation order) to present the standard
            // read/vocabulary/listen/speak/write rhythm consistently, rather
            // than an unspecified DB row order.
            'dayTasks' => fn (HasMany $query) => $query->orderBy('id'),
            'dayTasks.vocabularyItems',
        ]);

        $learnerProgram = $request->user()->learnerProgram;

        $completedTaskIds = $learnerProgram === null
            ? []
            : LearnerDayTask::query()
                ->where('learner_program_id', $learnerProgram->id)
                ->whereIn('day_task_id', $day->dayTasks->pluck('id'))
                ->pluck('day_task_id')
                ->all();

        return Inertia::render('days/show', [
            'day' => [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'title' => $day->title,
            ],
            'week' => [
                'id' => $day->week->id,
                'week_number' => $day->week->week_number,
                'title' => $day->week->title,
            ],
            'month' => [
                'id' => $day->month->id,
                'month_number' => $day->month->month_number,
                'theme' => $day->month->theme,
            ],
            'tasks' => $day->dayTasks->map(fn (DayTask $task): array => [
                'id' => $task->id,
                'type' => $task->type->value,
                'content' => $task->content,
                'estimated_minutes' => $task->estimated_minutes,
                'completed' => in_array($task->id, $completedTaskIds, true),
                'vocabulary_items' => $task->vocabularyItems
                    ->sortBy('order')
                    ->values()
                    ->map(fn (DayTaskVocabularyItem $item): array => [
                        'id' => $item->id,
                        'term' => $item->term,
                        'native_meaning' => $item->native_meaning,
                    ]),
            ]),
            'canToggleTasks' => $learnerProgram !== null && $request->user()->id === $learnerProgram->user_id,
        ]);
    }
}
