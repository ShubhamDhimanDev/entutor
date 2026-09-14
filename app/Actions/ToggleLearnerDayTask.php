<?php

namespace App\Actions;

use App\Models\DayTask;
use App\Models\LearnerDayTask;
use App\Models\LearnerProgram;

class ToggleLearnerDayTask
{
    /**
     * Mark a day task complete or incomplete for a learner's program.
     *
     * Both directions are idempotent: completing an already-complete task,
     * or uncompleting one that was never completed, is a no-op rather than
     * an error.
     */
    public function handle(LearnerProgram $learnerProgram, DayTask $dayTask, bool $complete): void
    {
        if ($complete) {
            LearnerDayTask::query()->firstOrCreate([
                'learner_program_id' => $learnerProgram->id,
                'day_task_id' => $dayTask->id,
            ]);

            return;
        }

        LearnerDayTask::query()
            ->where('learner_program_id', $learnerProgram->id)
            ->where('day_task_id', $dayTask->id)
            ->delete();
    }
}
