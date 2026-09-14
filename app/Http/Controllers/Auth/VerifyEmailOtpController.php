<?php

namespace App\Http\Controllers\Auth;

use App\Actions\IssueEmailOtp;
use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VerifyEmailOtpController extends Controller
{
    /**
     * Show the email verification (one-time code) page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Verify the submitted one-time code.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $otp = EmailOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp || $otp->attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => $otp !== null
                    ? 'Too many attempts. Please request a new code.'
                    : 'No active code found. Please request a new one.',
            ]);
        }

        if (! Hash::check($validated['code'], $otp->code_hash)) {
            $otp->increment('attempts');

            $remaining = max(0, 5 - $otp->attempts);

            throw ValidationException::withMessages([
                'code' => $remaining > 0
                    ? "That code is incorrect. {$remaining} attempt(s) remaining."
                    : 'Too many attempts. Please request a new code.',
            ]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();

        $user->markEmailAsVerified();

        return redirect()->route('dashboard');
    }

    /**
     * Issue a fresh one-time code.
     */
    public function resend(Request $request, IssueEmailOtp $issueEmailOtp): RedirectResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $issueEmailOtp->handle($user);

        return back()->with('status', 'otp-sent');
    }

    /**
     * Resolve the authenticated user. The 'auth' middleware guarantees one is present.
     */
    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }
}
