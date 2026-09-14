<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\LearnerProgram;
use App\Models\User;
use App\Models\Week;

test('a learner can record a weekly rating', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $response = $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 4,
        'speaking_rating' => 3,
        'listening_rating' => 5,
        'confidence_rating' => 4,
        'note' => 'Good progress.',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_weekly_ratings', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'reading_rating' => 4,
        'speaking_rating' => 3,
        'listening_rating' => 5,
        'confidence_rating' => 4,
        'note' => 'Good progress.',
        'rated_by_user_id' => $learner->id,
    ]);
});

test('recording a rating for the same week again updates it in place', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 2,
        'speaking_rating' => 2,
        'listening_rating' => 2,
        'confidence_rating' => 2,
    ]);

    $response = $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 5,
        'speaking_rating' => 5,
        'listening_rating' => 5,
        'confidence_rating' => 5,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_weekly_ratings', 1);
    $this->assertDatabaseHas('learner_weekly_ratings', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'reading_rating' => 5,
    ]);
});

test('a rating below 1 fails validation', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $response = $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 0,
        'speaking_rating' => 3,
        'listening_rating' => 3,
        'confidence_rating' => 3,
    ]);

    $response->assertSessionHasErrors('reading_rating');
    $this->assertDatabaseCount('learner_weekly_ratings', 0);
});

test('a rating above 5 fails validation', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $response = $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 3,
        'speaking_rating' => 6,
        'listening_rating' => 3,
        'confidence_rating' => 3,
    ]);

    $response->assertSessionHasErrors('speaking_rating');
    $this->assertDatabaseCount('learner_weekly_ratings', 0);
});

test('the boundary values 1 and 5 are both valid', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $week = Week::factory()->create();

    $response = $this->actingAs($learner)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 1,
        'speaking_rating' => 5,
        'listening_rating' => 1,
        'confidence_rating' => 5,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('learner_weekly_ratings', 1);
});

test('an active coach can record a rating on behalf of their linked learner', function () {
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

    $response = $this->actingAs($coach)->post(route('coach.learners.weeks.rating.store', [$program, $week]), [
        'reading_rating' => 4,
        'speaking_rating' => 4,
        'listening_rating' => 4,
        'confidence_rating' => 4,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('learner_weekly_ratings', [
        'learner_program_id' => $program->id,
        'week_id' => $week->id,
        'rated_by_user_id' => $coach->id,
    ]);
});

test('a stranger coach cannot record a rating for a learner they are not linked to', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $program = LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $stranger = User::factory()->create(['role' => UserRole::Coach]);
    $week = Week::factory()->create();

    $response = $this->actingAs($stranger)->post(route('coach.learners.weeks.rating.store', [$program, $week]), [
        'reading_rating' => 3,
        'speaking_rating' => 3,
        'listening_rating' => 3,
        'confidence_rating' => 3,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('learner_weekly_ratings', 0);
});

test('a stranger cannot record a rating for a learner they are not linked to', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    LearnerProgram::factory()->create(['user_id' => $learner->id]);
    $stranger = User::factory()->create(['role' => UserRole::Coach]);
    $week = Week::factory()->create();

    // The stranger has no LearnerProgram of their own, so the learner-only
    // route 404s; the meaningful boundary is exercised via the coach-scoped
    // route in the coach authorization test below.
    $response = $this->actingAs($stranger)->post(route('weeks.rating.store', $week), [
        'reading_rating' => 3,
        'speaking_rating' => 3,
        'listening_rating' => 3,
        'confidence_rating' => 3,
    ]);

    $response->assertStatus(404);
});
