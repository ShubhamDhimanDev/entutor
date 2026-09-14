<?php

use App\Actions\ComputeLearnerDailyProgress;
use App\Enums\SkillArea;
use App\Enums\UserRole;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\LearnerDayTask;
use App\Models\LearnerProgram;
use App\Models\User;
use App\Models\Week;

/**
 * months.month_number and weeks.week_number both have real unique DB
 * constraints backing a fixed 6-month/24-week curriculum, and
 * MonthFactory/WeekFactory reflect that with fake()->unique() over that
 * exact fixed range — so tests that need several Day rows must share one
 * Week (and its Month) rather than letting Day::factory()'s default
 * week_id/month_id spin up a fresh one per call.
 */
function seedDayWithTasks(Week $week, int $dayNumber, int $taskCount = 5): Day
{
    $day = Day::factory()->create([
        'day_number' => $dayNumber,
        'week_id' => $week->id,
        'month_id' => $week->month_id,
    ]);

    // day_tasks has a unique(day_id, type) constraint — assign distinct
    // SkillArea values rather than the factory's random (collision-prone) pick.
    foreach (array_slice(SkillArea::cases(), 0, $taskCount) as $type) {
        DayTask::factory()->create(['day_id' => $day->id, 'type' => $type]);
    }

    return $day->fresh('dayTasks');
}

test('a day only counts as done once every one of its tasks is complete', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);

    $week = Week::factory()->create();
    $day = seedDayWithTasks($week, 1, 5);

    // Complete only 4 of 5 tasks.
    $day->dayTasks->take(4)->each(fn (DayTask $task) => LearnerDayTask::factory()->create([
        'learner_program_id' => $program->id,
        'day_task_id' => $task->id,
    ]));

    $result = app(ComputeLearnerDailyProgress::class)->handle($program);

    expect($result['daysDone'])->toBe(0);
    expect($result['totalDays'])->toBe(1);
    expect($result['currentDayNumber'])->toBe(1);

    // Complete the 5th and final task.
    LearnerDayTask::factory()->create([
        'learner_program_id' => $program->id,
        'day_task_id' => $day->dayTasks->last()->id,
    ]);

    $result = app(ComputeLearnerDailyProgress::class)->handle($program);

    expect($result['daysDone'])->toBe(1);
    expect($result['currentDayNumber'])->toBeNull();
});

test('the current day is the lowest-numbered incomplete day, not the lowest day overall', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);

    $week = Week::factory()->create();
    $day1 = seedDayWithTasks($week, 1, 2);
    $day2 = seedDayWithTasks($week, 2, 2);
    seedDayWithTasks($week, 3, 2);

    // Fully complete day 1.
    $day1->dayTasks->each(fn (DayTask $task) => LearnerDayTask::factory()->create([
        'learner_program_id' => $program->id,
        'day_task_id' => $task->id,
    ]));

    // Partially complete day 2.
    LearnerDayTask::factory()->create([
        'learner_program_id' => $program->id,
        'day_task_id' => $day2->dayTasks->first()->id,
    ]);

    $result = app(ComputeLearnerDailyProgress::class)->handle($program);

    expect($result['daysDone'])->toBe(1);
    expect($result['totalDays'])->toBe(3);
    expect($result['currentDayNumber'])->toBe(2);
});

test('a learner who has completed every seeded day is fully caught up, not an error', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);

    $week = Week::factory()->create();
    $day1 = seedDayWithTasks($week, 1, 3);
    $day2 = seedDayWithTasks($week, 2, 3);

    foreach ([$day1, $day2] as $day) {
        $day->dayTasks->each(fn (DayTask $task) => LearnerDayTask::factory()->create([
            'learner_program_id' => $program->id,
            'day_task_id' => $task->id,
        ]));
    }

    $result = app(ComputeLearnerDailyProgress::class)->handle($program);

    expect($result['daysDone'])->toBe(2);
    expect($result['totalDays'])->toBe(2);
    expect($result['currentDayNumber'])->toBeNull();
});

test('the dashboard route reflects the rollup for the authenticated learner', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);

    seedDayWithTasks(Week::factory()->create(), 1, 2);

    $response = $this->actingAs($learner)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('daysDone', 0)
        ->where('totalDays', 1)
        ->where('currentDay.day_number', 1)
    );
});

test('the shared progressSummary prop is present for a learner and null for a coach', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    seedDayWithTasks(Week::factory()->create(), 1, 2);

    $this->actingAs($learner)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('progressSummary.totalDays', 1)
            ->where('progressSummary.daysDone', 0)
        );

    $this->actingAs($coach)
        ->get(route('coach.learners.index'))
        ->assertInertia(fn ($page) => $page->where('progressSummary', null));
});
