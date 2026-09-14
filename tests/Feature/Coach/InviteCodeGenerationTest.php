<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the coach settings page is displayed for a learner with no coach linked', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    $this->actingAs($learner)
        ->get(route('coach.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/coach')
            ->where('coachLink', null),
        );
});

test('a learner can generate an invite code', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    $response = $this->actingAs($learner)->post(route('coach.invite-code.store'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('coach.edit'));

    $this->assertDatabaseHas('coach_links', [
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending->value,
    ]);
});

test('generating an invite code is idempotent while a pending code is still valid', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    $this->actingAs($learner)->post(route('coach.invite-code.store'));
    $first = CoachLink::query()->where('learner_user_id', $learner->id)->firstOrFail();

    $this->actingAs($learner)->post(route('coach.invite-code.store'));
    $second = CoachLink::query()->where('learner_user_id', $learner->id)->firstOrFail();

    expect(CoachLink::query()->where('learner_user_id', $learner->id)->count())->toBe(1);
    expect($second->invite_code)->toBe($first->invite_code);
});

test('a fresh invite code is generated once the previous one has expired', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'status' => CoachLinkStatus::Pending,
        'invite_code' => 'EXPIRED1',
        'invite_code_expires_at' => now()->subDay(),
    ]);

    $this->actingAs($learner)->post(route('coach.invite-code.store'));

    expect(CoachLink::query()->where('learner_user_id', $learner->id)->count())->toBe(2);

    $fresh = CoachLink::query()
        ->where('learner_user_id', $learner->id)
        ->where('invite_code', '!=', 'EXPIRED1')
        ->firstOrFail();

    expect($fresh->status)->toBe(CoachLinkStatus::Pending);
    expect($fresh->invite_code_expires_at->isFuture())->toBeTrue();
});

test('generating an invite code is rejected while a coach is already active', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coach->id,
        'status' => CoachLinkStatus::Active,
        'redeemed_at' => now(),
    ]);

    $response = $this->actingAs($learner)->post(route('coach.invite-code.store'));

    $response->assertSessionHasErrors('coach');

    expect(CoachLink::query()->where('learner_user_id', $learner->id)->count())->toBe(1);
});

test('a coach cannot generate an invite code', function () {
    $coach = User::factory()->create(['role' => UserRole::Coach]);

    $response = $this->actingAs($coach)->post(route('coach.invite-code.store'));

    $response->assertForbidden();

    $this->assertDatabaseCount('coach_links', 0);
});
