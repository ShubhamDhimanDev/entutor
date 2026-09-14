<?php

use App\Enums\CoachLinkStatus;
use App\Enums\SkillArea;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\MonthlyTest;
use App\Models\User;

test('a learner can record a monthly test score with no sub-scores', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $response = $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'taken_on' => '2026-01-15',
        'total_score' => 72,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_monthly_test_records', [
        'learner_program_id' => $program->id,
        'month_id' => $month->id,
        'total_score' => 72,
        'recorded_by_user_id' => $learner->id,
    ]);
    // The `date` cast serializes with the connection's default datetime
    // format on write (a Laravel default, not app-specific behaviour) —
    // assert the meaningful value via the Carbon accessor instead of the
    // raw column string.
    expect($program->monthlyTestRecords()->first()->taken_on->toDateString())->toBe('2026-01-15');
    $this->assertDatabaseCount('learner_monthly_test_record_scores', 0);
});

test('a learner can record a monthly test score with partial sub-scores', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $response = $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 60,
        'scores' => [
            ['skill' => SkillArea::Reading->value, 'score' => 70],
            ['skill' => SkillArea::Speaking->value, 'score' => 50],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_monthly_test_record_scores', 2);

    $record = $program->monthlyTestRecords()->first();
    expect($record->scores)->toHaveCount(2);
});

test('a learner can record a monthly test score with a full set of sub-scores', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $scores = collect(SkillArea::cases())->map(fn (SkillArea $skill) => [
        'skill' => $skill->value,
        'score' => 65,
    ])->all();

    $response = $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 65,
        'scores' => $scores,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_monthly_test_record_scores', count(SkillArea::cases()));
});

test('recording a test for the same month again updates the record and upserts sub-scores', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 50,
        'scores' => [['skill' => SkillArea::Reading->value, 'score' => 40]],
    ]);

    $response = $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 90,
        'scores' => [['skill' => SkillArea::Reading->value, 'score' => 95]],
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_monthly_test_records', 1);
    $this->assertDatabaseCount('learner_monthly_test_record_scores', 1);
    $this->assertDatabaseHas('learner_monthly_test_records', [
        'learner_program_id' => $program->id,
        'month_id' => $month->id,
        'total_score' => 90,
    ]);
    $this->assertDatabaseHas('learner_monthly_test_record_scores', [
        'skill' => SkillArea::Reading->value,
        'score' => 95,
    ]);
});

test('an active coach can record a monthly test result on behalf of their linked learner', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $month = Month::factory()->create();

    $response = $this->actingAs($coach)->post(route('coach.learners.months.test-record.store', [$program, $month]), [
        'total_score' => 80,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_monthly_test_records', [
        'learner_program_id' => $program->id,
        'month_id' => $month->id,
        'recorded_by_user_id' => $coach->id,
    ]);
});

test('a stranger coach cannot record a monthly test result for a learner they are not linked to', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $stranger = User::factory()->create(['role' => UserRole::Coach]);
    $month = Month::factory()->create();

    $response = $this->actingAs($stranger)->post(route('coach.learners.months.test-record.store', [$program, $month]), [
        'total_score' => 80,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('learner_monthly_test_records', 0);
});

test('total_score above 100 fails validation', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $response = $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 101,
    ]);

    $response->assertSessionHasErrors('total_score');
});

dataset('band boundaries', [
    'exactly 80 is excellent' => [80, 'excellent'],
    'exactly 60 is good' => [60, 'good'],
    'exactly 40 is fair' => [40, 'fair'],
    '39 is needs work' => [39, 'needs_work'],
]);

test('the band is derived correctly at exact boundary values', function (int $score, string $expectedBand) {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();
    MonthlyTest::factory()->create([
        'month_id' => $month->id,
        'band_excellent_min' => 80,
        'band_good_min' => 60,
        'band_fair_min' => 40,
    ]);

    $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => $score,
    ]);

    $record = $program->monthlyTestRecords()->where('month_id', $month->id)->first();

    expect($record->band->value)->toBe($expectedBand);
})->with('band boundaries');

test('the band falls back to the default 80/60/40 thresholds when no MonthlyTest row exists', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $month = Month::factory()->create();

    $this->actingAs($learner)->post(route('months.test-record.store', $month), [
        'total_score' => 45,
    ]);

    $record = $program->monthlyTestRecords()->where('month_id', $month->id)->first();

    expect($record->band->value)->toBe('fair');
});
