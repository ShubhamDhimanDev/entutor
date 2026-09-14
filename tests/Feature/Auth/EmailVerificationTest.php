<?php

use App\Actions\IssueEmailOtp;
use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

/**
 * Issue an OTP for the given user and capture the plaintext code that was
 * sent, mirroring what a user would read out of their inbox.
 */
function issueOtpAndCaptureCode(User $user): string
{
    app(IssueEmailOtp::class)->handle($user);

    $code = null;

    Notification::assertSentTo($user, EmailOtpNotification::class, function ($notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    expect($code)->not->toBeNull();

    return $code;
}

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertOk();
});

test('verified user is redirected to dashboard from verification prompt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('correct code verifies email and redirects to dashboard', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $code = issueOtpAndCaptureCode($user);

    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => $code,
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('incorrect code fails verification and increments attempts', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    issueOtpAndCaptureCode($user);

    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();

    $otp = EmailOtp::query()->where('user_id', $user->id)->whereNull('consumed_at')->latest('id')->first();
    expect($otp->attempts)->toBe(1);
});

test('fifth wrong attempt locks the code out', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $code = issueOtpAndCaptureCode($user);

    for ($i = 0; $i < 5; $i++) {
        $response = $this->actingAs($user)->post(route('verification.store'), [
            'code' => '000000',
        ]);
    }

    $response->assertSessionHasErrors('code');
    expect(session('errors')->get('code')[0])->toContain('Too many attempts');

    // Even the correct code is now rejected until the user requests a new one.
    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => $code,
    ]);

    $response->assertSessionHasErrors('code');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('expired code is rejected', function () {
    $user = User::factory()->unverified()->create();

    EmailOtp::factory()->for($user)->create([
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('code');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('no active code returns a helpful error', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('code');
    expect(session('errors')->get('code')[0])->toContain('No active code');
});

test('code is required and must be 6 digits', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post(route('verification.store'), [
        'code' => '12',
    ]);

    $response->assertSessionHasErrors('code');
});
