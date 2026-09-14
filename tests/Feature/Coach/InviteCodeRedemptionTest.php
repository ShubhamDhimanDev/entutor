<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the redeem page is displayed', function () {
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $this->actingAs($coach)
        ->get(route('coach.redeem.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('coach/redeem'));
});

test('a coach can redeem a valid pending invite code', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $coachLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'ABCD1234',
        'invite_code_expires_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($coach)->post(route('coach.redeem.store'), [
        'code' => 'ABCD1234',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('coach.learners.index'));

    $coachLink->refresh();

    expect($coachLink->status)->toBe(CoachLinkStatus::Active);
    expect($coachLink->coach_user_id)->toBe($coach->id);
    expect($coachLink->redeemed_at)->not->toBeNull();
});

test('redeeming rejects an expired code', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'EXPIRED1',
        'invite_code_expires_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($coach)->post(route('coach.redeem.store'), [
        'code' => 'EXPIRED1',
    ]);

    $response->assertSessionHasErrors('code');

    $this->assertDatabaseHas('coach_links', [
        'invite_code' => 'EXPIRED1',
        'status' => CoachLinkStatus::Pending->value,
    ]);
});

test('redeeming rejects an unknown code', function () {
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $response = $this->actingAs($coach)->post(route('coach.redeem.store'), [
        'code' => 'NOPE0000',
    ]);

    $response->assertSessionHasErrors('code');
});

test('redeeming rejects an already-redeemed code', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $firstCoach = User::factory()->create(['role' => UserRole::Coach]);
    $secondCoach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $firstCoach->id,
        'status' => CoachLinkStatus::Active,
        'invite_code' => 'REDEEMED',
        'invite_code_expires_at' => now()->addDays(7),
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($secondCoach)->post(route('coach.redeem.store'), [
        'code' => 'REDEEMED',
    ]);

    // Surfaced as a generic validation error rather than a 403: an already-
    // consumed code must look the same to the caller as a code that never
    // existed, or a second coach probing invite codes could learn which
    // ones are "real" (and already spoken for) versus made up.
    $response->assertSessionHasErrors('code');
});

test('a learner cannot redeem an invite code', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $otherLearner = User::factory()->create(['role' => UserRole::Learner]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'WRONGROL',
        'invite_code_expires_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($otherLearner)->post(route('coach.redeem.store'), [
        'code' => 'WRONGROL',
    ]);

    // Same reasoning as the already-redeemed case: a learner probing a real,
    // still-pending code must not be able to tell "wrong role" apart from
    // "no such code" via the response.
    $response->assertSessionHasErrors('code');
});

test('redeeming rejects a code for a learner who already has an active coach', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $existingCoach = User::factory()->create(['role' => UserRole::Coach]);
    $racingCoach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $existingCoach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    // A second, still-pending invite for the same learner (e.g. generated
    // before the first invite was redeemed).
    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'SECONDONE',
        'invite_code_expires_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($racingCoach)->post(route('coach.redeem.store'), [
        'code' => 'SECONDONE',
    ]);

    $response->assertSessionHasErrors('code');
});

test('two rapid redemption attempts against the same pending code only let one succeed', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $firstCoach = User::factory()->create(['role' => UserRole::Coach]);
    $secondCoach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'RACECODE',
        'invite_code_expires_at' => now()->addDays(7),
    ]);

    // Assert on each response immediately after its request: session-based
    // assertions read the *current* session, so checking them out of order
    // would let the second request's flashed errors bleed into the
    // assertion for the first.
    $firstResponse = $this->actingAs($firstCoach)->post(route('coach.redeem.store'), [
        'code' => 'RACECODE',
    ]);
    $firstResponse->assertSessionHasNoErrors();

    $secondResponse = $this->actingAs($secondCoach)->post(route('coach.redeem.store'), [
        'code' => 'RACECODE',
    ]);
    $secondResponse->assertSessionHasErrors('code');

    expect(
        CoachLink::query()
            ->where('invite_code', 'RACECODE')
            ->where('status', CoachLinkStatus::Active)
            ->count(),
    )->toBe(1);
});

test('redemption failures look identical to the caller regardless of the underlying reason', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $existingCoach = User::factory()->create(['role' => UserRole::Coach]);
    $probingCoach = User::factory()->create(['role' => UserRole::Coach]);
    $probingLearner = User::factory()->create(['role' => UserRole::Learner]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $existingCoach->id,
        'status' => CoachLinkStatus::Active,
        'invite_code' => 'REALCODE',
        'invite_code_expires_at' => now()->addDays(7),
        'redeemed_at' => now(),
    ]);

    // Made up, never issued.
    $unknownResponse = $this->actingAs($probingCoach)->post(route('coach.redeem.store'), ['code' => 'MADEUP01']);
    $unknownResponse->assertSessionHasErrors('code');
    $unknownMessage = session('errors')->get('code')[0];

    // Real code, but already redeemed by someone else (403-shaped failure
    // before this fix).
    $alreadyRedeemedResponse = $this->actingAs($probingCoach)->post(route('coach.redeem.store'), ['code' => 'REALCODE']);
    $alreadyRedeemedResponse->assertSessionHasErrors('code');
    $alreadyRedeemedMessage = session('errors')->get('code')[0];

    // Real, still-pending code, but the wrong role (403-shaped failure
    // before this fix).
    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'PENDING01',
        'invite_code_expires_at' => now()->addDays(7),
    ]);
    $wrongRoleResponse = $this->actingAs($probingLearner)->post(route('coach.redeem.store'), ['code' => 'PENDING01']);
    $wrongRoleResponse->assertSessionHasErrors('code');
    $wrongRoleMessage = session('errors')->get('code')[0];

    expect($unknownMessage)
        ->toBe($alreadyRedeemedMessage)
        ->toBe($wrongRoleMessage);
});
