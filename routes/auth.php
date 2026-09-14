<?php

use App\Http\Controllers\Auth\VerifyEmailOtpController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('verify-email', [VerifyEmailOtpController::class, 'show'])->name('verification.notice');
    Route::post('verify-email', [VerifyEmailOtpController::class, 'store'])->middleware('throttle:email-otp-verify')->name('verification.store');
    Route::post('verify-email/resend', [VerifyEmailOtpController::class, 'resend'])->middleware('throttle:email-otp-resend')->name('verification.send');
});
