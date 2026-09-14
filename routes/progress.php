<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\LearnerDayTaskController;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\MonthlyTestController;
use App\Http\Controllers\WeeklyMilestoneController;
use App\Http\Controllers\WeeklyRatingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('days/{day}', [DayController::class, 'show'])->name('days.show');
    Route::post('days/{day}/tasks/{dayTask}/complete', [LearnerDayTaskController::class, 'store'])
        ->name('days.tasks.complete');
    Route::delete('days/{day}/tasks/{dayTask}/complete', [LearnerDayTaskController::class, 'destroy'])
        ->name('days.tasks.uncomplete');

    Route::get('months/{month}', [MonthController::class, 'show'])->name('months.show');
    Route::get('months/{month}/test', [MonthlyTestController::class, 'show'])->name('months.test.show');
    Route::post('months/{month}/test-record', [MonthlyTestController::class, 'store'])
        ->name('months.test-record.store');

    Route::post('weeks/{week}/milestone/confirm', [WeeklyMilestoneController::class, 'store'])
        ->name('weeks.milestone.confirm');
    Route::post('weeks/{week}/rating', [WeeklyRatingController::class, 'store'])->name('weeks.rating.store');
});
