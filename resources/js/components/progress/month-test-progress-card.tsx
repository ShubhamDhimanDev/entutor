import { Form } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getBandBadgeClassName } from '@/lib/band-badge';
import type { MonthTestProgress, SkillArea } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const SKILLS: Array<{ value: SkillArea; label: string }> = [
    { value: 'reading', label: 'Reading' },
    { value: 'vocabulary', label: 'Vocabulary' },
    { value: 'listening', label: 'Listening' },
    { value: 'speaking', label: 'Speaking' },
    { value: 'writing', label: 'Writing' },
];

export function MonthTestProgressCard({
    month,
    testRecordForm,
}: {
    month: MonthTestProgress;
    testRecordForm: RouteFormDefinition<'post'>;
}) {
    return (
        <Card data-test={`month-test-card-${month.id}`}>
            <CardHeader>
                <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                    <span>
                        Month {month.month_number}: {month.theme}
                    </span>
                    {month.record && (
                        <Badge
                            variant="outline"
                            className={getBandBadgeClassName(month.record.band)}
                        >
                            {month.record.total_score}/100 —{' '}
                            {month.record.band_label}
                        </Badge>
                    )}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    {...testRecordForm}
                    options={{ preserveScroll: true }}
                    className="grid gap-3 sm:grid-cols-2"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1.5">
                                <Label htmlFor={`taken_on-${month.id}`}>
                                    Taken on
                                </Label>
                                <Input
                                    id={`taken_on-${month.id}`}
                                    name="taken_on"
                                    type="date"
                                    defaultValue={month.record?.taken_on}
                                />
                                <InputError message={errors.taken_on} />
                            </div>
                            <div className="grid gap-1.5">
                                <Label htmlFor={`total_score-${month.id}`}>
                                    Total score (0-100)
                                </Label>
                                <Input
                                    id={`total_score-${month.id}`}
                                    name="total_score"
                                    type="number"
                                    min={0}
                                    max={100}
                                    defaultValue={month.record?.total_score}
                                    required
                                />
                                <InputError message={errors.total_score} />
                            </div>

                            <Collapsible className="sm:col-span-2">
                                <CollapsibleTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="gap-1"
                                    >
                                        <ChevronDown className="size-3.5" />
                                        Per-skill scores (optional)
                                    </Button>
                                </CollapsibleTrigger>
                                <CollapsibleContent className="grid gap-3 pt-2 sm:grid-cols-2">
                                    {SKILLS.map((skill, index) => (
                                        <div
                                            key={skill.value}
                                            className="grid gap-1.5"
                                        >
                                            <Label
                                                htmlFor={`scores-${month.id}-${skill.value}`}
                                            >
                                                {skill.label}
                                            </Label>
                                            <input
                                                type="hidden"
                                                name={`scores[${index}][skill]`}
                                                value={skill.value}
                                            />
                                            <Input
                                                id={`scores-${month.id}-${skill.value}`}
                                                name={`scores[${index}][score]`}
                                                type="number"
                                                min={0}
                                                max={100}
                                            />
                                        </div>
                                    ))}
                                </CollapsibleContent>
                            </Collapsible>

                            <div className="sm:col-span-2">
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={processing}
                                    data-test={`save-test-record-${month.id}`}
                                >
                                    {month.record
                                        ? 'Update test record'
                                        : 'Record test result'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}
