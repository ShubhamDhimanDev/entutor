import { Form, Head } from '@inertiajs/react';
import MonthlyTestController from '@/actions/App/Http/Controllers/MonthlyTestController';
import InputError from '@/components/input-error';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getBandBadgeClassName } from '@/lib/band-badge';
import { cn } from '@/lib/utils';
import { show as showMonth } from '@/routes/months';
import { show as showMonthlyTest } from '@/routes/months/test';
import type { MonthlyTestRecord, MonthlyTestRubric, SkillArea } from '@/types';

type Month = { id: number; month_number: number; theme: string };

const SKILL_LABELS: Record<SkillArea, string> = {
    reading: 'Reading',
    vocabulary: 'Vocabulary',
    listening: 'Listening',
    speaking: 'Speaking',
    writing: 'Writing',
    grammar: 'Grammar',
};

export default function MonthlyTestShow({
    month,
    test,
    record,
    canAct,
}: {
    month: Month;
    test: MonthlyTestRubric | null;
    record: MonthlyTestRecord | null;
    canAct: boolean;
}) {
    const scoreFor = (skill: SkillArea) =>
        record?.scores.find((score) => score.skill === skill)?.score;

    return (
        <>
            <Head title={`Month ${month.month_number} test`} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="Monthly test"
                    description={`Month ${month.month_number}: ${month.theme}`}
                />

                {test ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Rubric</CardTitle>
                            <CardDescription>
                                Band guide: Excellent ≥{' '}
                                {test.band_excellent_min}, Good ≥{' '}
                                {test.band_good_min}, Fair ≥{' '}
                                {test.band_fair_min}, otherwise Needs work.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {test.sections
                                .slice()
                                .sort((a, b) => a.order - b.order)
                                .map((section) => (
                                    <div
                                        key={section.id}
                                        className="flex items-start justify-between gap-3 border-b pb-3 last:border-b-0 last:pb-0"
                                    >
                                        <div>
                                            <p className="text-sm font-medium">
                                                {SKILL_LABELS[section.skill]}
                                            </p>
                                            <p className="text-muted-foreground text-sm">
                                                {section.content}
                                            </p>
                                        </div>
                                        <Badge variant="secondary">
                                            {section.weight}%
                                        </Badge>
                                    </div>
                                ))}
                        </CardContent>
                    </Card>
                ) : (
                    <p className="text-muted-foreground text-sm">
                        No test rubric has been authored for this month yet.
                    </p>
                )}

                {record && (
                    <Card data-test="existing-test-record">
                        <CardHeader>
                            <CardTitle>Recorded result</CardTitle>
                            <CardDescription>
                                Taken on {record.taken_on}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-semibold">
                                {record.total_score}/100
                            </p>
                            <Badge
                                variant="outline"
                                className={cn(
                                    'mt-1',
                                    getBandBadgeClassName(record.band),
                                )}
                            >
                                {record.band_label}
                            </Badge>
                        </CardContent>
                    </Card>
                )}

                {canAct && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {record ? 'Update result' : 'Record result'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...MonthlyTestController.store.form(month.id)}
                                options={{ preserveScroll: true }}
                                className="grid gap-4 sm:grid-cols-2"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="taken_on">
                                                Taken on
                                            </Label>
                                            <Input
                                                id="taken_on"
                                                name="taken_on"
                                                type="date"
                                                defaultValue={record?.taken_on}
                                            />
                                            <InputError
                                                message={errors.taken_on}
                                            />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="total_score">
                                                Total score (0-100)
                                            </Label>
                                            <Input
                                                id="total_score"
                                                name="total_score"
                                                type="number"
                                                min={0}
                                                max={100}
                                                defaultValue={
                                                    record?.total_score
                                                }
                                                required
                                                data-test="total-score-input"
                                            />
                                            <InputError
                                                message={errors.total_score}
                                            />
                                        </div>

                                        {(
                                            Object.keys(
                                                SKILL_LABELS,
                                            ) as SkillArea[]
                                        ).map((skill, index) => (
                                            <div
                                                key={skill}
                                                className="grid gap-1.5"
                                            >
                                                <Label
                                                    htmlFor={`score-${skill}`}
                                                >
                                                    {SKILL_LABELS[skill]} score
                                                    (optional)
                                                </Label>
                                                <input
                                                    type="hidden"
                                                    name={`scores[${index}][skill]`}
                                                    value={skill}
                                                />
                                                <Input
                                                    id={`score-${skill}`}
                                                    name={`scores[${index}][score]`}
                                                    type="number"
                                                    min={0}
                                                    max={100}
                                                    defaultValue={scoreFor(
                                                        skill,
                                                    )}
                                                />
                                            </div>
                                        ))}

                                        <div className="sm:col-span-2">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                                data-test="save-test-record-button"
                                            >
                                                {record
                                                    ? 'Update result'
                                                    : 'Save result'}
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

MonthlyTestShow.layout = (props: { month: Month }) => ({
    breadcrumbs: [
        {
            title: `Month ${props.month.month_number}`,
            href: showMonth(props.month.id),
        },
        {
            title: 'Monthly test',
            href: showMonthlyTest(props.month.id),
        },
    ],
});
