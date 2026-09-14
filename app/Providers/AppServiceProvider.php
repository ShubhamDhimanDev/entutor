<?php

namespace App\Providers;

use App\Models\CoachLink;
use App\Models\LearnerDayTask;
use App\Models\LearnerMonthlyTestRecord;
use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyMilestone;
use App\Models\LearnerWeeklyRating;
use App\Policies\CoachLinkPolicy;
use App\Policies\LearnerDayTaskPolicy;
use App\Policies\LearnerProgramPolicy;
use App\Policies\MonthlyTestRecordPolicy;
use App\Policies\WeeklyMilestonePolicy;
use App\Policies\WeeklyRatingPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(CoachLink::class, CoachLinkPolicy::class);
        Gate::policy(LearnerProgram::class, LearnerProgramPolicy::class);
        Gate::policy(LearnerDayTask::class, LearnerDayTaskPolicy::class);
        Gate::policy(LearnerWeeklyMilestone::class, WeeklyMilestonePolicy::class);
        Gate::policy(LearnerWeeklyRating::class, WeeklyRatingPolicy::class);
        Gate::policy(LearnerMonthlyTestRecord::class, MonthlyTestRecordPolicy::class);

        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for non-Fortify, app-specific endpoints.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('coach-redeem', function (Request $request) {
            return Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
