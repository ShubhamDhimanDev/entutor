<?php

use App\Enums\CoachLinkStatus;
use App\Enums\SkillArea;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\LearnerProgram;
use App\Models\User;

function createDayWithTasks(int $taskCount = 5): Day
{
    $day = Day::factory()->create();

    // day_tasks has a unique(day_id, type) constraint, matching the real
    // curriculum's one-task-per-skill-per-day shape — assign distinct
    // SkillArea values rather than the factory's random (collision-prone) pick.
    foreach (array_slice(SkillArea::cases(), 0, $taskCount) as $type) {
        DayTask::factory()->create(['day_id' => $day->id, 'type' => $type]);
    }

    return $day->fresh('dayTasks');
}

test('a learner can complete a day task', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    $response = $this->actingAs($learner)->post(route('days.tasks.complete', [$day, $task]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_day_tasks', [
        'learner_program_id' => $program->id,
        'day_task_id' => $task->id,
    ]);
});

test('completing an already-complete task is idempotent', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    $this->actingAs($learner)->post(route('days.tasks.complete', [$day, $task]));
    $response = $this->actingAs($learner)->post(route('days.tasks.complete', [$day, $task]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_day_tasks', 1);
    $this->assertDatabaseHas('learner_day_tasks', [
        'learner_program_id' => $program->id,
        'day_task_id' => $task->id,
    ]);
});

test('a learner can uncomplete a day task', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    $this->actingAs($learner)->post(route('days.tasks.complete', [$day, $task]));
    $response = $this->actingAs($learner)->delete(route('days.tasks.uncomplete', [$day, $task]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_day_tasks', 0);
});

test('uncompleting a task that was never completed is idempotent', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    $response = $this->actingAs($learner)->delete(route('days.tasks.uncomplete', [$day, $task]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_day_tasks', 0);
});

test('a coach cannot toggle their own program via the learner-only route', function () {
    $coach = User::factory()->create(['role' => UserRole::Coach]);
    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    // A coach has no LearnerProgram of their own, so the "own program" route
    // 404s before authorization is even reached.
    $response = $this->actingAs($coach)->post(route('days.tasks.complete', [$day, $task]));

    $response->assertStatus(404);
    $this->assertDatabaseCount('learner_day_tasks', 0);
});

test('a coach cannot toggle a linked learner\'s day task even when actively linked', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $day = createDayWithTasks();
    $task = $day->dayTasks->first();

    // The coach-scoped route exists explicitly (routes/coach.php) so this is
    // an explicit policy denial (403), not an incidental 404 from a missing
    // route parameter.
    $response = $this->actingAs($coach)->post(route('coach.learners.days.tasks.complete', [$program, $day, $task]));

    $response->assertForbidden();
    $this->assertDatabaseCount('learner_day_tasks', 0);
});

test('a learner can complete and uncomplete a grammar day task', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = Day::factory()->create();
    $task = DayTask::factory()->grammar()->create(['day_id' => $day->id]);

    $completeResponse = $this->actingAs($learner)->post(route('days.tasks.complete', [$day, $task]));

    $completeResponse->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_day_tasks', [
        'learner_program_id' => $program->id,
        'day_task_id' => $task->id,
    ]);

    $uncompleteResponse = $this->actingAs($learner)->delete(route('days.tasks.uncomplete', [$day, $task]));

    $uncompleteResponse->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_day_tasks', 0);
});

test('a day task belonging to a different day 404s', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $dayA = createDayWithTasks();
    $dayB = createDayWithTasks();
    $taskFromDayB = $dayB->dayTasks->first();

    $response = $this->actingAs($learner)->post(route('days.tasks.complete', [$dayA, $taskFromDayB]));

    $response->assertStatus(404);
});
