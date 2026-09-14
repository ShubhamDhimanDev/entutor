<?php

use App\Http\Controllers\CoachLearnerController;
use App\Http\Controllers\CoachLinkController;
use App\Http\Controllers\LearnerDayTaskController;
use App\Http\Controllers\MonthlyTestController;
use App\Http\Controllers\WeeklyMilestoneController;
use App\Http\Controllers\WeeklyRatingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('coach')->name('coach.')->group(function () {
    Route::get('redeem', [CoachLinkController::class, 'create'])->name('redeem.create');
    Route::post('redeem', [CoachLinkController::class, 'store'])->middleware('throttle:coach-redeem')->name('redeem.store');
    Route::delete('links/{coachLink}', [CoachLinkController::class, 'destroy'])->name('links.destroy');

    Route::get('learners', [CoachLearnerController::class, 'index'])->name('learners.index');
    Route::get('learners/{learnerProgram}', [CoachLearnerController::class, 'show'])->name('learners.show');

    // Progress actions performed by a coach on a linked learner's behalf —
    // mirrors routes/progress.php's learner-side shape, scoped by
    // {learnerProgram} instead of the acting user's own program.
    Route::post('learners/{learnerProgram}/weeks/{week}/milestone/confirm', [WeeklyMilestoneController::class, 'storeForLearner'])
        ->name('learners.weeks.milestone.confirm');
    Route::post('learners/{learnerProgram}/weeks/{week}/rating', [WeeklyRatingController::class, 'storeForLearner'])
        ->name('learners.weeks.rating.store');
    Route::post('learners/{learnerProgram}/months/{month}/test-record', [MonthlyTestController::class, 'storeForLearner'])
        ->name('learners.months.test-record.store');

    // Explicitly present (not merely omitted) so LearnerDayTaskPolicy::toggle
    // denies a coach with a clean, testable 403 rather than relying on the
    // learner-side route's absence of a {learnerProgram} param to keep them
    // out incidentally.
    Route::post('learners/{learnerProgram}/days/{day}/tasks/{dayTask}/complete', [LearnerDayTaskController::class, 'storeForLearner'])
        ->name('learners.days.tasks.complete');
    Route::delete('learners/{learnerProgram}/days/{day}/tasks/{dayTask}/complete', [LearnerDayTaskController::class, 'destroyForLearner'])
        ->name('learners.days.tasks.uncomplete');
});
