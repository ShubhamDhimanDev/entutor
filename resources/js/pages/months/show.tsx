import { Head, Link } from '@inertiajs/react';
import WeeklyMilestoneController from '@/actions/App/Http/Controllers/WeeklyMilestoneController';
import WeeklyRatingController from '@/actions/App/Http/Controllers/WeeklyRatingController';
import { PageHeader } from '@/components/page-header';
import { WeekProgressCard } from '@/components/progress/week-progress-card';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show as showMonth } from '@/routes/months';
import { show as showMonthlyTest } from '@/routes/months/test';
import type { WeekProgress } from '@/types';

type Month = {
    id: number;
    month_number: number;
    theme: string;
    goals: string[];
    expected_outcomes: string[];
    daily_emphasis: string;
    speaking_practice_ideas: string[];
    reading_materials_ideas: string[];
    vocabulary_target: string;
};

export default function MonthShow({
    month,
    weeks,
    canAct,
}: {
    month: Month;
    weeks: WeekProgress[];
    canAct: boolean;
}) {
    return (
        <>
            <Head title={`Month ${month.month_number}: ${month.theme}`} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title={`Month ${month.month_number}: ${month.theme}`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={showMonthlyTest(month.id)}>
                                Monthly test
                            </Link>
                        </Button>
                    }
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Overview</CardTitle>
                        <CardDescription>
                            {month.daily_emphasis}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <h3 className="mb-1 text-sm font-medium">Goals</h3>
                            <ul className="text-muted-foreground list-disc space-y-1 pl-4 text-sm">
                                {month.goals.map((goal) => (
                                    <li key={goal}>{goal}</li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-1 text-sm font-medium">
                                Expected outcomes
                            </h3>
                            <ul className="text-muted-foreground list-disc space-y-1 pl-4 text-sm">
                                {month.expected_outcomes.map((outcome) => (
                                    <li key={outcome}>{outcome}</li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-1 text-sm font-medium">
                                Speaking practice ideas
                            </h3>
                            <ul className="text-muted-foreground list-disc space-y-1 pl-4 text-sm">
                                {month.speaking_practice_ideas.map((idea) => (
                                    <li key={idea}>{idea}</li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-1 text-sm font-medium">
                                Reading materials ideas
                            </h3>
                            <ul className="text-muted-foreground list-disc space-y-1 pl-4 text-sm">
                                {month.reading_materials_ideas.map((idea) => (
                                    <li key={idea}>{idea}</li>
                                ))}
                            </ul>
                        </div>
                        <div className="sm:col-span-2">
                            <h3 className="mb-1 text-sm font-medium">
                                Vocabulary target
                            </h3>
                            <p className="text-muted-foreground text-sm">
                                {month.vocabulary_target}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4">
                    {weeks.map((week) => (
                        <WeekProgressCard
                            key={week.id}
                            week={week}
                            canAct={canAct}
                            milestoneForm={WeeklyMilestoneController.store.form(
                                week.id,
                            )}
                            ratingForm={WeeklyRatingController.store.form(
                                week.id,
                            )}
                        />
                    ))}
                </div>
            </div>
        </>
    );
}

MonthShow.layout = (props: { month: Month }) => ({
    breadcrumbs: [
        {
            title: `Month ${props.month.month_number}`,
            href: showMonth(props.month.id),
        },
    ],
});
