<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\LearnerProgram;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a coach can view a linked learner\'s program', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($coach)->get(route('coach.learners.show', $program));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('coach/learners/show')
            ->where('learner.name', $learner->name)
            ->where('learner.email', $learner->email),
        );
});

test('the linked learner appears in the coach\'s learner list', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($coach)->get(route('coach.learners.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('coach/learners/index')
            ->has('learners', 1)
            ->where('learners.0.id', $program->id),
        );
});

test('an unlinked learner does not appear in the coach\'s learner list', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $response = $this->actingAs($coach)->get(route('coach.learners.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('coach/learners/index')
            ->has('learners', 0),
        );
});

test('a coach cannot view a learner they are not linked to', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $response = $this->actingAs($coach)->get(route('coach.learners.show', $program));

    $response->assertForbidden();
});

test('a coach cannot view a learner after the link is revoked', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Revoked,
        'redeemed_at' => now()->subDay(),
        'revoked_at' => now(),
        'revoked_by_user_id' => $learner->id,
    ]);

    $response = $this->actingAs($coach)->get(route('coach.learners.show', $program));

    $response->assertForbidden();
});

test('the learner themself manages their own program', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);

    expect($program->isManagedBy($learner))->toBeTrue();
});

test('an unrelated coach does not manage a learner program', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    expect($program->isManagedBy($coach))->toBeFalse();
});
