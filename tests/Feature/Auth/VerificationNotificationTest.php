<?php

use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Notification;

test('resend issues a fresh verification code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect();

    Notification::assertSentTo($user, EmailOtpNotification::class);

    expect(EmailOtp::query()->where('user_id', $user->id)->whereNull('consumed_at')->count())->toBe(1);
});

test('resend does not issue a code if email is already verified', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});

test('resend respects the one per minute rate limiter', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'))->assertRedirect();

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertTooManyRequests();
});

test('verify endpoint requires authentication', function () {
    $response = $this->post(route('verification.store'), ['code' => '123456']);

    $response->assertRedirect(route('login'));
});

test('resend endpoint requires authentication', function () {
    $response = $this->post(route('verification.send'));

    $response->assertRedirect(route('login'));
});

test('verification notice requires authentication', function () {
    $response = $this->get(route('verification.notice'));

    $response->assertRedirect(route('login'));
});
