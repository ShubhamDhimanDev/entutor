<?php

namespace App\Http\Controllers\Settings;

use App\Actions\GenerateCoachInviteCode;
use App\Enums\CoachLinkStatus;
use App\Http\Controllers\Controller;
use App\Models\CoachLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoachSettingsController extends Controller
{
    /**
     * Show the learner's coach-linkage settings page.
     */
    public function edit(Request $request): Response
    {
        $coachLink = CoachLink::query()
            ->where('learner_user_id', $request->user()->id)
            ->whereIn('status', [CoachLinkStatus::Pending, CoachLinkStatus::Active])
            ->with('coach')
            ->latest('id')
            ->first();

        return Inertia::render('settings/coach', [
            'coachLink' => $coachLink === null ? null : [
                'id' => $coachLink->id,
                'status' => $coachLink->status,
                'invite_code' => $coachLink->status === CoachLinkStatus::Pending
                    ? $coachLink->invite_code
                    : null,
                'invite_code_expires_at' => $coachLink->invite_code_expires_at?->toIso8601String(),
                'is_expired' => $coachLink->status === CoachLinkStatus::Pending
                    && $coachLink->invite_code_expires_at !== null
                    && $coachLink->invite_code_expires_at->isPast(),
                'coach_name' => $coachLink->coach?->name,
                'coach_email' => $coachLink->coach?->email,
            ],
        ]);
    }

    /**
     * Generate (or return the existing pending) invite code for the learner.
     */
    public function store(Request $request, GenerateCoachInviteCode $action): RedirectResponse
    {
        $this->authorize('create', CoachLink::class);

        $action->handle($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invite code ready to share.')]);

        return to_route('coach.edit');
    }
}
