<?php

namespace App\Actions;

use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Hash;

class IssueEmailOtp
{
    /**
     * Invalidate any outstanding one-time codes for the user and issue a fresh one.
     */
    public function handle(User $user): EmailOtp
    {
        EmailOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100_000, 999_999);

        $otp = EmailOtp::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new EmailOtpNotification($code));

        return $otp;
    }
}
