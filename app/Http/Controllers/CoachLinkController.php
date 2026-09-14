<?php

namespace App\Http\Controllers;

use App\Actions\RedeemCoachInviteCode;
use App\Actions\RevokeCoachLink;
use App\Models\CoachLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CoachLinkController extends Controller
{
    /**
     * Show the coach's invite-code redemption form.
     */
    public function create(): Response
    {
        return Inertia::render('coach/redeem');
    }

    /**
     * Redeem an invite code, linking the authenticated coach to a learner.
     *
     * Every rejection reason (code not found, wrong role, wrong status,
     * expired, learner already linked elsewhere) is surfaced to the caller
     * as the same generic validation error. Distinguishing them would let
     * anyone who holds or guesses a code string learn another learner's
     * coach-link status (e.g. "this code is real but that learner already
     * has a coach") without being a party to that relationship. The
     * underlying authorization/business checks are unchanged — only the
     * externally-visible response is unified.
     */
    public function store(Request $request, RedeemCoachInviteCode $action): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $invalidCode = fn (): ValidationException => ValidationException::withMessages([
            'code' => 'That invite code is invalid or has expired.',
        ]);

        $coachLink = CoachLink::query()
            ->where('invite_code', $validated['code'])
            ->first();

        if ($coachLink === null) {
            throw $invalidCode();
        }

        try {
            $this->authorize('redeem', $coachLink);
        } catch (AuthorizationException) {
            throw $invalidCode();
        }

        try {
            $action->handle($request->user(), $coachLink->invite_code);
        } catch (ValidationException) {
            throw $invalidCode();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Coach link established.')]);

        return to_route('coach.learners.index');
    }

    /**
     * Revoke a coach link. Either the learner or the coach on the link may do this.
     */
    public function destroy(Request $request, CoachLink $coachLink, RevokeCoachLink $action): RedirectResponse
    {
        $this->authorize('revoke', $coachLink);

        $action->handle($coachLink, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Coach link revoked.')]);

        return back();
    }
}
