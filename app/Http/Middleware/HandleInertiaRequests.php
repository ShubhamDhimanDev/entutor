<?php

namespace App\Http\Middleware;

use App\Actions\ComputeLearnerDailyProgress;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'progressSummary' => $this->progressSummary($user),
        ];
    }

    /**
     * The learner's cheap day-progress summary, shared globally so nav/UI
     * chrome can reference it without every page having to fetch it. Null
     * for anyone who isn't a learner (including a learner with no program
     * yet, which should not happen in practice but must not 500 the whole
     * app if it does).
     *
     * @return array{daysDone: int, totalDays: int, currentDayNumber: int|null}|null
     */
    private function progressSummary(?User $user): ?array
    {
        if ($user === null || $user->role !== UserRole::Learner) {
            return null;
        }

        $learnerProgram = $user->learnerProgram;

        if ($learnerProgram === null) {
            return null;
        }

        $daily = app(ComputeLearnerDailyProgress::class)->handle($learnerProgram);

        return [
            'daysDone' => $daily['daysDone'],
            'totalDays' => $daily['totalDays'],
            'currentDayNumber' => $daily['currentDayNumber'],
        ];
    }
}
