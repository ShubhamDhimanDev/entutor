import { Form, Head } from '@inertiajs/react';
import {
    BookOpen,
    CheckSquare,
    Headphones,
    Mic,
    PenLine,
    Square,
    SpellCheck,
} from 'lucide-react';
import { type ComponentType } from 'react';
import LearnerDayTaskController from '@/actions/App/Http/Controllers/LearnerDayTaskController';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { show as showDay } from '@/routes/days';
import { show as showMonth } from '@/routes/months';
import type { DayTask, SkillArea } from '@/types';

type Day = { id: number; day_number: number; title: string };
type Week = { id: number; week_number: number; title: string };
type Month = { id: number; month_number: number; theme: string };

const SKILL_META: Record<
    SkillArea,
    { label: string; icon: ComponentType<{ className?: string }> }
> = {
    reading: { label: 'Reading', icon: BookOpen },
    vocabulary: { label: 'Vocabulary', icon: SpellCheck },
    listening: { label: 'Listening', icon: Headphones },
    speaking: { label: 'Speaking', icon: Mic },
    writing: { label: 'Writing', icon: PenLine },
};

export default function DayShow({
    day,
    week,
    month,
    tasks,
    canToggleTasks,
}: {
    day: Day;
    week: Week;
    month: Month;
    tasks: DayTask[];
    canToggleTasks: boolean;
}) {
    return (
        <>
            <Head title={`Day ${day.day_number}: ${day.title}`} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title={`Day ${day.day_number}: ${day.title}`}
                    description={`Month ${month.month_number}: ${month.theme} · Week ${week.week_number}: ${week.title}`}
                />

                <div className="grid gap-4">
                    {tasks.map((task) => {
                        const meta = SKILL_META[task.type];
                        const Icon = meta.icon;
                        const toggleForm = task.completed
                            ? LearnerDayTaskController.destroy.form({
                                  day: day.id,
                                  dayTask: task.id,
                              })
                            : LearnerDayTaskController.store.form({
                                  day: day.id,
                                  dayTask: task.id,
                              });

                        return (
                            <Card
                                key={task.id}
                                data-test={`task-${task.id}`}
                                className={cn(
                                    task.completed &&
                                        'border-l-success border-l-4',
                                )}
                            >
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between gap-2">
                                        <span className="flex items-center gap-2">
                                            <Icon className="text-muted-foreground size-4" />
                                            {meta.label}
                                        </span>
                                        <span className="text-muted-foreground text-xs font-normal">
                                            ~{task.estimated_minutes} min
                                        </span>
                                    </CardTitle>
                                    <CardDescription>
                                        {task.content}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {task.vocabulary_items.length > 0 && (
                                        <ul className="grid gap-1 text-sm sm:grid-cols-2">
                                            {task.vocabulary_items.map(
                                                (item) => (
                                                    <li key={item.id}>
                                                        <span className="font-medium">
                                                            {item.term}
                                                        </span>{' '}
                                                        — {item.native_meaning}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    )}

                                    {canToggleTasks ? (
                                        <Form
                                            {...toggleForm}
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    variant={
                                                        task.completed
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                    disabled={processing}
                                                    data-test={`toggle-task-${task.id}-button`}
                                                    className="gap-1.5"
                                                >
                                                    {task.completed ? (
                                                        <CheckSquare className="text-success size-4" />
                                                    ) : (
                                                        <Square className="size-4" />
                                                    )}
                                                    {task.completed
                                                        ? 'Complete'
                                                        : 'Mark complete'}
                                                </Button>
                                            )}
                                        </Form>
                                    ) : (
                                        task.completed && (
                                            <p className="text-muted-foreground flex items-center gap-1.5 text-sm">
                                                <CheckSquare className="text-success size-4" />
                                                Complete
                                            </p>
                                        )
                                    )}
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>
        </>
    );
}

DayShow.layout = (props: { day: Day; week: Week; month: Month }) => ({
    breadcrumbs: [
        {
            title: `Month ${props.month.month_number}`,
            href: showMonth(props.month.id),
        },
        {
            title: `Day ${props.day.day_number}`,
            href: showDay(props.day.id),
        },
    ],
});
