import { Head, Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    CalendarCheck,
    ClipboardCheck,
    Flame,
    Headphones,
    type LucideIcon,
    Mic,
    Sparkles,
    Star,
} from 'lucide-react';
import { motion } from 'motion/react';
import { DayGrid } from '@/components/progress/day-grid';
import { StaggerContainer, StaggerItem } from '@/components/motion/stagger';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { springSnappy } from '@/lib/motion';
import { dashboard } from '@/routes';
import { show as showDay } from '@/routes/days';
import { show as showMonth } from '@/routes/months';
import type { Auth, DashboardData, DaySummary } from '@/types';

type PageProps = {
    auth: Auth;
};

/** Trailing count of consecutive `done: true` days at the end of `days`. */
function computeStreak(days: DaySummary[]): number {
    let streak = 0;

    for (let i = days.length - 1; i >= 0; i--) {
        if (!days[i].done) {
            break;
        }

        streak++;
    }

    return streak;
}

/** Percentage, clamped to [0, 100] and guarded against a zero total. */
function progressPercent(done: number, total: number): number {
    if (total <= 0) {
        return 0;
    }

    return Math.min(100, Math.max(0, Math.round((done / total) * 100)));
}

type StatTileProps = {
    icon: LucideIcon;
    label: string;
    value: string;
    progress: number;
    dataTest?: string;
};

function StatTile({
    icon: Icon,
    label,
    value,
    progress,
    dataTest,
}: StatTileProps) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-sm font-medium">
                    <Icon className="text-muted-foreground size-4" />
                    {label}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                <p
                    className="text-3xl leading-none font-bold tabular-nums"
                    data-test={dataTest}
                >
                    {value}
                </p>
                <Progress value={progress} />
            </CardContent>
        </Card>
    );
}

function StreakTile({ streak }: { streak: number }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-sm font-medium">
                    <Flame className="text-chart-3 size-4" />
                    Current streak
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-1">
                {streak > 0 ? (
                    <>
                        <p className="text-3xl leading-none font-bold tabular-nums">
                            {streak}
                        </p>
                        <p className="text-muted-foreground text-sm">
                            day streak
                        </p>
                    </>
                ) : (
                    <p className="text-muted-foreground text-sm">
                        Start your streak today
                    </p>
                )}
            </CardContent>
        </Card>
    );
}

export default function Dashboard(props: DashboardData) {
    const {
        program,
        daysDone,
        totalDays,
        weeksTicked,
        totalWeeks,
        testsRecorded,
        totalTests,
        currentDay,
        days,
        months,
        recentRatings,
    } = props;

    const { auth } = usePage<PageProps>().props;

    const streak = computeStreak(days);
    const dayContext = daysDone + (currentDay ? 1 : 0);

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-2xl font-semibold tracking-tight">
                            Good to see you again, {auth.user.name}
                        </CardTitle>
                        <CardDescription>
                            Day {dayContext} of {totalDays}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-lg font-semibold">
                                {currentDay
                                    ? `Pick up on Day ${currentDay.day_number}`
                                    : "You're fully caught up!"}
                            </p>
                            <p className="text-muted-foreground text-sm">
                                {currentDay
                                    ? currentDay.title
                                    : 'Every seeded day is complete. Check back as new months are added.'}
                            </p>
                        </div>
                        {currentDay && (
                            <motion.span
                                className="inline-block"
                                whileTap={{ scale: 0.98 }}
                                transition={springSnappy}
                            >
                                <Button
                                    asChild
                                    size="lg"
                                    data-test="continue-day-button"
                                >
                                    <Link href={showDay(currentDay.id)}>
                                        Continue Day {currentDay.day_number}
                                    </Link>
                                </Button>
                            </motion.span>
                        )}
                    </CardContent>
                </Card>

                <StaggerContainer
                    className="grid gap-4 md:grid-cols-4"
                    gap={0.07}
                >
                    <StaggerItem>
                        <StatTile
                            icon={CalendarCheck}
                            label="Days complete"
                            value={`${daysDone} / ${totalDays}`}
                            progress={progressPercent(daysDone, totalDays)}
                            dataTest="days-done-stat"
                        />
                    </StaggerItem>
                    <StaggerItem>
                        <StatTile
                            icon={ClipboardCheck}
                            label="Weeks ticked"
                            value={`${weeksTicked} / ${totalWeeks}`}
                            progress={progressPercent(weeksTicked, totalWeeks)}
                        />
                    </StaggerItem>
                    <StaggerItem>
                        <StatTile
                            icon={Star}
                            label="Tests recorded"
                            value={`${testsRecorded} / ${totalTests}`}
                            progress={progressPercent(
                                testsRecorded,
                                totalTests,
                            )}
                        />
                    </StaggerItem>
                    <StaggerItem>
                        <StreakTile streak={streak} />
                    </StaggerItem>
                </StaggerContainer>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Your progress</CardTitle>
                            <CardDescription>
                                Program started {program.start_date}. Each
                                square is one day — filled squares are complete.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <DayGrid days={days} />
                        </CardContent>
                    </Card>

                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardTitle>Months</CardTitle>
                            <CardDescription>
                                Jump to a month's overview, weekly milestones,
                                and test.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2">
                            {months.map((month) => (
                                <Button
                                    key={month.id}
                                    variant="outline"
                                    size="sm"
                                    className="w-full justify-start"
                                    asChild
                                >
                                    <Link href={showMonth(month.id)}>
                                        Month {month.month_number}
                                    </Link>
                                </Button>
                            ))}
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Recent weekly ratings</CardTitle>
                            <CardDescription>
                                Your most recently recorded self/coach ratings.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {recentRatings.length === 0 ? (
                                <p className="text-muted-foreground text-sm">
                                    No ratings recorded yet.
                                </p>
                            ) : (
                                recentRatings.map((rating) => (
                                    <div
                                        key={rating.week_id}
                                        className="flex items-center justify-between gap-2 text-sm"
                                    >
                                        <span>
                                            Week {rating.week_number}:{' '}
                                            {rating.week_title}
                                        </span>
                                        <div className="flex gap-1">
                                            <Badge
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <BookOpen className="size-3" />
                                                {rating.reading_rating}
                                            </Badge>
                                            <Badge
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <Mic className="size-3" />
                                                {rating.speaking_rating}
                                            </Badge>
                                            <Badge
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <Headphones className="size-3" />
                                                {rating.listening_rating}
                                            </Badge>
                                            <Badge
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <Sparkles className="size-3" />
                                                {rating.confidence_rating}
                                            </Badge>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
