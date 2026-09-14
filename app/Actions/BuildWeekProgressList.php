<?php

namespace App\Actions;

use App\Models\LearnerProgram;
use App\Models\LearnerWeeklyMilestone;
use App\Models\LearnerWeeklyRating;
use App\Models\Week;
use Illuminate\Database\Eloquent\Collection;

class BuildWeekProgressList
{
    /**
     * Annotate a set of weeks with this learner's milestone/rating state, in
     * two grouped queries regardless of how many weeks are passed in.
     *
     * @param  Collection<int, Week>  $weeks
     * @return array<int, array<string, mixed>>
     */
    public function handle(LearnerProgram $learnerProgram, Collection $weeks): array
    {
        $weekIds = $weeks->pluck('id');

        $milestones = LearnerWeeklyMilestone::query()
            ->where('learner_program_id', $learnerProgram->id)
            ->whereIn('week_id', $weekIds)
            ->get()
            ->keyBy('week_id');

        $ratings = LearnerWeeklyRating::query()
            ->where('learner_program_id', $learnerProgram->id)
            ->whereIn('week_id', $weekIds)
            ->get()
            ->keyBy('week_id');

        return $weeks
            ->map(function (Week $week) use ($milestones, $ratings): array {
                /** @var LearnerWeeklyMilestone|null $milestone */
                $milestone = $milestones->get($week->id);
                /** @var LearnerWeeklyRating|null $rating */
                $rating = $ratings->get($week->id);

                return [
                    'id' => $week->id,
                    'week_number' => $week->week_number,
                    'week_in_month' => $week->week_in_month,
                    'month_id' => $week->month_id,
                    'title' => $week->title,
                    'mastery_description' => $week->mastery_description,
                    'start_day_number' => $week->start_day_number,
                    'end_day_number' => $week->end_day_number,
                    'milestone_confirmed' => $milestone !== null,
                    'milestone_confirmed_at' => $milestone?->created_at?->toIso8601String(),
                    'rating' => $rating === null ? null : [
                        'reading_rating' => $rating->reading_rating,
                        'speaking_rating' => $rating->speaking_rating,
                        'listening_rating' => $rating->listening_rating,
                        'confidence_rating' => $rating->confidence_rating,
                        'note' => $rating->note,
                        'updated_at' => $rating->updated_at?->toIso8601String(),
                    ],
                ];
            })
            ->values()
            ->all();
    }
}
