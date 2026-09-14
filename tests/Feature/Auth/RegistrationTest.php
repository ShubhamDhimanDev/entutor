<?php

use App\Enums\UserRole;
use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registering issues exactly one email OTP, not one per duplicate event listener', function () {
    // Regression test: the Registered event is wired to send a verification
    // notification both by Laravel's own default (Illuminate\Foundation\Support\
    // Providers\EventServiceProvider::configureEmailVerification(), registered
    // automatically by the framework on every boot) and would be wired a second
    // time if anything in this app's own providers also calls
    // Event::listen(Registered::class, SendEmailVerificationNotification::class)
    // explicitly. Doing both breaks real users: two codes get emailed for one
    // registration, and only the second is actually valid, so a user who reads
    // the first email out of their inbox gets "That code is incorrect."
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Single Otp User',
        'email' => 'single-otp@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'single-otp@example.com')->firstOrFail();

    expect(EmailOtp::query()->where('user_id', $user->id)->count())->toBe(1);
    Notification::assertSentToTimes($user, EmailOtpNotification::class, 1);
});

test('registering without a role defaults to learner and creates a learner program', function () {
    $this->post(route('register.store'), [
        'name' => 'Default Role User',
        'email' => 'default-role@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'default-role@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Learner);
    expect($user->learnerProgram()->exists())->toBeTrue();
});

test('registering as a coach does not create a learner program', function () {
    $this->post(route('register.store'), [
        'name' => 'Coach User',
        'email' => 'coach@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => UserRole::Coach->value,
    ]);

    $user = User::query()->where('email', 'coach@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Coach);
    expect($user->learnerProgram()->exists())->toBeFalse();
});

test('registering with an invalid role is rejected', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Bad Role User',
        'email' => 'bad-role@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});
