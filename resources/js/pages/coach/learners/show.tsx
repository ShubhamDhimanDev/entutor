import { Head } from '@inertiajs/react';
import { CalendarCheck, ClipboardCheck, Star } from 'lucide-react';
import WeeklyMilestoneController from '@/actions/App/Http/Controllers/WeeklyMilestoneController';
import WeeklyRatingController from '@/actions/App/Http/Controllers/WeeklyRatingController';
import MonthlyTestController from '@/actions/App/Http/Controllers/MonthlyTestController';
import { DayGrid } from '@/components/progress/day-grid';
import { MonthTestProgressCard } from '@/components/progress/month-test-progress-card';
import { WeekProgressCard } from '@/components/progress/week-progress-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { index, show } from '@/routes/coach/learners';
import type { DashboardData, MonthTestProgress, WeekProgress } from '@/types';

type Learner = {
    id: number;
    name: string;
    email: string;
    start_date: string;
};

export default function LearnerShow({
    learner,
    dashboard,
    weeks,
    months,
}: {
    learner: Learner;
    dashboard: DashboardData;
    weeks: WeekProgress[];
    months: MonthTestProgress[];
}) {
    const percent = (done: number, total: number) =>
        total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;

    return (
        <>
            <Head title={learner.name} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{learner.name}</CardTitle>
                    </CardHeader>
                    <CardContent className="text-muted-foreground grid gap-1 text-sm">
                        <p>{learner.email}</p>
                        <p>Program start date: {learner.start_date}</p>
                    </CardContent>
                </Card>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium">
                                <CalendarCheck className="text-muted-foreground size-4" />
                                Days complete
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <p className="text-3xl leading-none font-bold tabular-nums">
                                {dashboard.daysDone} / {dashboard.totalDays}
                            </p>
                            <Progress
                                value={percent(
                                    dashboard.daysDone,
                                    dashboard.totalDays,
                                )}
                            />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium">
                                <ClipboardCheck className="text-muted-foreground size-4" />
                                Weeks ticked
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <p className="text-3xl leading-none font-bold tabular-nums">
                                {dashboard.weeksTicked} / {dashboard.totalWeeks}
                            </p>
                            <Progress
                                value={percent(
                                    dashboard.weeksTicked,
                                    dashboard.totalWeeks,
                                )}
                            />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium">
                                <Star className="text-muted-foreground size-4" />
                                Tests recorded
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <p className="text-3xl leading-none font-bold tabular-nums">
                                {dashboard.testsRecorded} /{' '}
                                {dashboard.totalTests}
                            </p>
                            <Progress
                                value={percent(
                                    dashboard.testsRecorded,
                                    dashboard.totalTests,
                                )}
                            />
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Day-by-day progress</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DayGrid days={dashboard.days} />
                        <p className="text-muted-foreground mt-3 text-xs">
                            Read-only — only {learner.name} can mark their own
                            daily tasks complete.
                        </p>
                    </CardContent>
                </Card>

                <div>
                    <h2 className="mb-2 text-lg font-semibold tracking-tight">
                        Weekly milestones and ratings
                    </h2>
                    <div className="grid gap-4">
                        {weeks.map((week) => (
                            <WeekProgressCard
                                key={week.id}
                                week={week}
                                canAct
                                milestoneForm={WeeklyMilestoneController.storeForLearner.form(
                                    {
                                        learnerProgram: learner.id,
                                        week: week.id,
                                    },
                                )}
                                ratingForm={WeeklyRatingController.storeForLearner.form(
                                    {
                                        learnerProgram: learner.id,
                                        week: week.id,
                                    },
                                )}
                            />
                        ))}
                    </div>
                </div>

                <div>
                    <h2 className="mb-2 text-lg font-semibold tracking-tight">
                        Monthly tests
                    </h2>
                    <div className="grid gap-4">
                        {months.map((month) => (
                            <MonthTestProgressCard
                                key={month.id}
                                month={month}
                                testRecordForm={MonthlyTestController.storeForLearner.form(
                                    {
                                        learnerProgram: learner.id,
                                        month: month.id,
                                    },
                                )}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

LearnerShow.layout = (props: { learner: Learner }) => ({
    breadcrumbs: [
        {
            title: 'My learners',
            href: index(),
        },
        {
            title: props.learner.name,
            href: show({ learnerProgram: props.learner.id }),
        },
    ],
});
