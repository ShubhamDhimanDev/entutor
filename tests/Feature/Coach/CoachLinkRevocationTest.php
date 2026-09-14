<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\User;

test('a learner can revoke their active coach link', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($learner)->delete(route('coach.links.destroy', $coachLink));

    $response->assertSessionHasNoErrors();

    $coachLink->refresh();

    expect($coachLink->status)->toBe(CoachLinkStatus::Revoked);
    expect($coachLink->revoked_at)->not->toBeNull();
    expect($coachLink->revoked_by_user_id)->toBe($learner->id);
});

test('a coach can revoke their active link', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($coach)->delete(route('coach.links.destroy', $coachLink));

    $response->assertSessionHasNoErrors();

    $coachLink->refresh();

    expect($coachLink->status)->toBe(CoachLinkStatus::Revoked);
    expect($coachLink->revoked_by_user_id)->toBe($coach->id);
});

test('a learner can cancel their own pending invite', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
    ]);

    $response = $this->actingAs($learner)->delete(route('coach.links.destroy', $coachLink));

    $response->assertSessionHasNoErrors();
    expect($coachLink->refresh()->status)->toBe(CoachLinkStatus::Revoked);
});

test('revoking keeps the row as an audit trail rather than deleting it', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $this->actingAs($learner)->delete(route('coach.links.destroy', $coachLink));

    $this->assertDatabaseHas('coach_links', ['id' => $coachLink->id]);
    $this->assertDatabaseCount('coach_links', 1);
});

test('a stranger cannot revoke a coach link', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);
    $stranger = User::factory()->create(['role' => UserRole::Coach]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($stranger)->delete(route('coach.links.destroy', $coachLink));

    $response->assertForbidden();

    expect($coachLink->refresh()->status)->toBe(CoachLinkStatus::Active);
});
