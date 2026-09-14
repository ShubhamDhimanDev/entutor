<?php

use App\Enums\CoachLinkStatus;
use App\Enums\SkillArea;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\LearnerDayTask;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\MonthlyTest;
use App\Models\MonthlyTestSection;
use App\Models\Tense;
use App\Models\User;
use App\Models\Week;

test('a learner can view a day with per-task completion state', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = Day::factory()->create();
    // day_tasks has a unique(day_id, type) constraint — assign distinct
    // SkillArea values rather than the factory's random (collision-prone) pick.
    $tasks = collect(SkillArea::cases())->map(
        fn (SkillArea $type) => DayTask::factory()->create(['day_id' => $day->id, 'type' => $type])
    );

    LearnerDayTask::factory()->create([
        'learner_program_id' => $program->id,
        'day_task_id' => $tasks->first()->id,
    ]);

    $response = $this->actingAs($learner)->get(route('days.show', $day));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('days/show')
        ->has('tasks', 6)
        ->where('tasks.0.completed', true)
        ->where('tasks.1.completed', false)
        ->where('canToggleTasks', true)
    );
});

test('a month page shows its weeks with milestone and rating state', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();
    Week::factory()->count(4)->create(['month_id' => $month->id]);

    $response = $this->actingAs($learner)->get(route('months.show', $month));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('months/show')
        ->has('weeks', 4)
        ->where('canAct', true)
    );
});

test('a monthly test page shows the rubric and existing record', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();
    $monthlyTest = MonthlyTest::factory()->create(['month_id' => $month->id]);
    MonthlyTestSection::factory()->create([
        'monthly_test_id' => $monthlyTest->id,
        'skill' => SkillArea::Speaking,
    ]);

    $response = $this->actingAs($learner)->get(route('months.test.show', $month));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('months/test')
        ->has('test.sections', 1)
        ->where('record', null)
    );
});

test('a day payload includes the tense for a grammar task and null for other tasks', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $day = Day::factory()->create();
    $tense = Tense::factory()->create(['name' => 'Past Simple']);
    $grammarTask = DayTask::factory()->grammar()->create(['day_id' => $day->id, 'tense_id' => $tense->id]);
    $readingTask = DayTask::factory()->create(['day_id' => $day->id, 'type' => SkillArea::Reading]);

    $response = $this->actingAs($learner)->get(route('days.show', $day));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('days/show')
        ->has('tasks', 2)
        ->where('tasks.0.id', $grammarTask->id)
        ->where('tasks.0.type', 'grammar')
        ->where('tasks.0.tense.id', $tense->id)
        ->where('tasks.0.tense.key', $tense->key->value)
        ->where('tasks.0.tense.name', 'Past Simple')
        ->where('tasks.1.id', $readingTask->id)
        ->where('tasks.1.tense', null)
    );
});

test('a month page shows its distinct grammar tenses, deduped and in canonical order', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();
    $week = Week::factory()->create(['month_id' => $month->id]);

    // Deliberately create the later-order tense first, so a naive
    // creation-order (rather than tense.order) sort would fail this test.
    $tenseLater = Tense::factory()->create(['order' => 5]);
    $tenseEarlier = Tense::factory()->create(['order' => 2]);

    $dayOne = Day::factory()->create(['week_id' => $week->id, 'month_id' => $month->id, 'day_number' => 1]);
    $dayTwo = Day::factory()->create(['week_id' => $week->id, 'month_id' => $month->id, 'day_number' => 2]);
    $dayThree = Day::factory()->create(['week_id' => $week->id, 'month_id' => $month->id, 'day_number' => 3]);

    DayTask::factory()->grammar()->create(['day_id' => $dayOne->id, 'tense_id' => $tenseLater->id]);
    DayTask::factory()->grammar()->create(['day_id' => $dayTwo->id, 'tense_id' => $tenseEarlier->id]);
    // The same tense taught again later in the month must be deduped, not doubled.
    DayTask::factory()->grammar()->create(['day_id' => $dayThree->id, 'tense_id' => $tenseLater->id]);

    $response = $this->actingAs($learner)->get(route('months.show', $month));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('months/show')
        ->has('tenses', 2)
        ->where('tenses.0.id', $tenseEarlier->id)
        ->where('tenses.1.id', $tenseLater->id)
    );
});

test('a month page with no grammar tasks yet has an empty tenses list', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();
    Week::factory()->count(2)->create(['month_id' => $month->id]);

    $response = $this->actingAs($learner)->get(route('months.show', $month));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('tenses', 0));
});

test('a coach viewing a linked learner sees the full progress shape', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    // Tie the weeks to the same 2 months explicitly — Week::factory()'s
    // default month_id would otherwise spin up 2 *additional* implicit
    // months, which would both violate months.month_number's fixed 1-6
    // range across enough tests and throw off the exact counts asserted below.
    $months = Month::factory()->count(2)->create();
    Week::factory()->create(['month_id' => $months->first()->id]);
    Week::factory()->create(['month_id' => $months->last()->id]);

    $response = $this->actingAs($coach)->get(route('coach.learners.show', $program));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('coach/learners/show')
        ->has('dashboard')
        ->has('weeks', 2)
        ->has('months', 2)
    );
});
