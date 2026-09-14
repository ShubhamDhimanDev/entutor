<?php

use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use App\Models\CoachLink;
use App\Models\User;
use Illuminate\Database\QueryException;

test('the database rejects a second active coach link for the same learner', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coachA = User::factory()->create(['role' => UserRole::Coach]);
    $coachB = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coachA->id,
        'status' => CoachLinkStatus::Active,
        'invite_code' => 'UNIQUE01',
        'redeemed_at' => now(),
    ]);

    expect(fn () => CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coachB->id,
        'status' => CoachLinkStatus::Active,
        'invite_code' => 'UNIQUE02',
        'redeemed_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('the database allows a new active link once the previous one is revoked', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);
    $coachA = User::factory()->create(['role' => UserRole::Coach]);
    $coachB = User::factory()->create(['role' => UserRole::Coach]);

    CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coachA->id,
        'status' => CoachLinkStatus::Revoked,
        'invite_code' => 'REVOKED1',
        'redeemed_at' => now()->subDay(),
        'revoked_at' => now(),
        'revoked_by_user_id' => $learner->id,
    ]);

    $secondLink = CoachLink::factory()->create([
        'learner_user_id' => $learner->id,
        'coach_user_id' => $coachB->id,
        'status' => CoachLinkStatus::Active,
        'invite_code' => 'ACTIVE02',
        'redeemed_at' => now(),
    ]);

    expect($secondLink->exists)->toBeTrue();
    $this->assertDatabaseCount('coach_links', 2);
});
