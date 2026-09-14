<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\LearnerProgram;
use App\Models\User;
use App\Models\Week;

test('a learner can confirm a weekly milestone for their own program', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $response = $this->actingAs($learner)->post(route('weeks.milestone.confirm', $week), [
        'note' => 'Felt confident this week.',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_weekly_milestones', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'confirmed_by_user_id' => $learner->id,
        'note' => 'Felt confident this week.',
    ]);
});

test('an active coach can confirm a milestone on behalf of their linked learner', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $week = Week::factory()->create();

    $response = $this->actingAs($coach)->post(route('coach.learners.weeks.milestone.confirm', [$program, $week]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_weekly_milestones', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'confirmed_by_user_id' => $coach->id,
    ]);
});

test('a stranger coach cannot confirm a milestone for a learner they are not linked to', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $stranger = User::factory()->create(['role' => UserRole::Coach]);

    $week = Week::factory()->create();

    $response = $this->actingAs($stranger)->post(route('coach.learners.weeks.milestone.confirm', [$program, $week]));

    $response->assertForbidden();
    $this->assertDatabaseCount('learner_weekly_milestones', 0);
});

test('confirming an already-confirmed milestone is idempotent and keeps the original confirmer', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $week = Week::factory()->create();

    $this->actingAs($learner)->post(route('weeks.milestone.confirm', $week));
    $this->actingAs($coach)->post(route('coach.learners.weeks.milestone.confirm', [$program, $week]));

    $this->assertDatabaseCount('learner_weekly_milestones', 1);
    $this->assertDatabaseHas('learner_weekly_milestones', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'confirmed_by_user_id' => $learner->id,
    ]);
});
