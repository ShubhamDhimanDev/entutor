<?php

namespace App\Actions;

use App\Models\Day;
use App\Models\LearnerProgram;
use App\Models\Month;
use App\Models\Week;

class BuildLearnerDashboardData
{
    public function __construct(private readonly ComputeLearnerDailyProgress $computeLearnerDailyProgress)
    {
        //
    }

    /**
     * Build the full learner-dashboard data shape: the day-level rollup plus
     * weeks/tests summaries, the current-day pointer, a per-day grid for the
     * 180-day visual, a lightweight month nav list, and recent ratings.
     *
     * Reused as-is for a coach viewing a linked learner's read-only progress.
     *
     * @return array<string, mixed>
     */
    public function handle(LearnerProgram $learnerProgram): array
    {
        $daily = $this->computeLearnerDailyProgress->handle($learnerProgram);
        $incompleteDayNumbers = array_flip($daily['incompleteDayNumbers']);

        $days = Day::query()
            ->orderBy('day_number')
            ->get(['id', 'day_number', 'title', 'week_id', 'month_id'])
            ->map(fn (Day $day): array => [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'title' => $day->title,
                'week_id' => $day->week_id,
                'month_id' => $day->month_id,
                'done' => ! array_key_exists($day->day_number, $incompleteDayNumbers),
            ]);

        $currentDay = $daily['currentDayNumber'] === null
            ? null
            : $days->firstWhere('day_number', $daily['currentDayNumber']);

        $totalWeeks = Week::query()->count();
        $totalTests = Month::query()->count();

        $recentRatings = $learnerProgram->weeklyRatings()
            ->with('week')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($rating): array => [
                'week_id' => $rating->week_id,
                'week_number' => $rating->week->week_number,
                'week_title' => $rating->week->title,
                'reading_rating' => $rating->reading_rating,
                'speaking_rating' => $rating->speaking_rating,
                'listening_rating' => $rating->listening_rating,
                'confidence_rating' => $rating->confidence_rating,
                'note' => $rating->note,
                'updated_at' => $rating->updated_at?->toIso8601String(),
            ])
            ->values();

        $months = Month::query()
            ->orderBy('month_number')
            ->get(['id', 'month_number', 'theme'])
            ->map(fn (Month $month): array => [
                'id' => $month->id,
                'month_number' => $month->month_number,
                'theme' => $month->theme,
            ])
            ->values();

        return [
            'program' => [
                'id' => $learnerProgram->id,
                'start_date' => $learnerProgram->start_date->toDateString(),
            ],
            'daysDone' => $daily['daysDone'],
            'totalDays' => $daily['totalDays'],
            'weeksTicked' => $learnerProgram->weeklyMilestones()->count(),
            'totalWeeks' => $totalWeeks,
            'testsRecorded' => $learnerProgram->monthlyTestRecords()->count(),
            'totalTests' => $totalTests,
            'currentDay' => $currentDay,
            'days' => $days->values(),
            'months' => $months,
            'recentRatings' => $recentRatings,
        ];
    }
}
