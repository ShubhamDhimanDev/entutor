<?php

namespace App\Http\Controllers;

use App\Actions\ToggleLearnerDayTask;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\LearnerDayTask;
use App\Models\LearnerProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LearnerDayTaskController extends Controller
{
    /**
     * Mark a day task complete for the current user's own program.
     */
    public function store(Request $request, Day $day, DayTask $dayTask, ToggleLearnerDayTask $action): RedirectResponse
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return $this->toggle($learnerProgram, $day, $dayTask, $action, complete: true);
    }

    /**
     * Mark a day task incomplete for the current user's own program.
     */
    public function destroy(Request $request, Day $day, DayTask $dayTask, ToggleLearnerDayTask $action): RedirectResponse
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return $this->toggle($learnerProgram, $day, $dayTask, $action, complete: false);
    }

    /**
     * Coach-side route (`routes/coach.php`) to the same action, scoped by an
     * explicit {learnerProgram}. This route exists specifically so that a
     * coach is excluded by an explicit, testable 403 from
     * LearnerDayTaskPolicy::toggle — not merely by the absence of a route
     * that would let them address a specific learner's tasks.
     */
    public function storeForLearner(LearnerProgram $learnerProgram, Day $day, DayTask $dayTask, ToggleLearnerDayTask $action): RedirectResponse
    {
        return $this->toggle($learnerProgram, $day, $dayTask, $action, complete: true);
    }

    /**
     * Coach-side counterpart to storeForLearner(); see its docblock.
     */
    public function destroyForLearner(LearnerProgram $learnerProgram, Day $day, DayTask $dayTask, ToggleLearnerDayTask $action): RedirectResponse
    {
        return $this->toggle($learnerProgram, $day, $dayTask, $action, complete: false);
    }

    private function toggle(LearnerProgram $learnerProgram, Day $day, DayTask $dayTask, ToggleLearnerDayTask $action, bool $complete): RedirectResponse
    {
        abort_unless($dayTask->day_id === $day->id, 404);

        $this->authorize('toggle', [LearnerDayTask::class, $learnerProgram]);

        $action->handle($learnerProgram, $dayTask, $complete);

        return back();
    }
}
