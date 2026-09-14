import { Form } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import InputError from '@/components/input-error';
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
import { Textarea } from '@/components/ui/textarea';
import type { WeekProgress } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const RATING_FIELDS = [
    { name: 'reading_rating', label: 'Reading' },
    { name: 'speaking_rating', label: 'Speaking' },
    { name: 'listening_rating', label: 'Listening' },
    { name: 'confidence_rating', label: 'Confidence' },
] as const;

export function WeekProgressCard({
    week,
    milestoneForm,
    ratingForm,
    canAct,
}: {
    week: WeekProgress;
    milestoneForm: RouteFormDefinition<'post'>;
    ratingForm: RouteFormDefinition<'post'>;
    canAct: boolean;
}) {
    return (
        <Card data-test={`week-card-${week.id}`}>
            <CardHeader>
                <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                    <span>
                        Week {week.week_number}: {week.title}
                    </span>
                    {week.milestone_confirmed && (
                        <Badge
                            variant="outline"
                            className="border-success/40 bg-success/10 text-success gap-1"
                        >
                            <CheckCircle2 className="size-3.5" />
                            Milestone confirmed
                        </Badge>
                    )}
                </CardTitle>
                <CardDescription>{week.mastery_description}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <p className="text-muted-foreground text-xs">
                    Days {week.start_day_number}–{week.end_day_number}
                </p>

                {canAct && !week.milestone_confirmed && (
                    <Form {...milestoneForm} options={{ preserveScroll: true }}>
                        {({ processing }) => (
                            <Button
                                type="submit"
                                size="sm"
                                variant="outline"
                                disabled={processing}
                                data-test={`confirm-milestone-${week.id}`}
                            >
                                Confirm milestone
                            </Button>
                        )}
                    </Form>
                )}

                {canAct && (
                    <Form
                        {...ratingForm}
                        options={{ preserveScroll: true }}
                        className="bg-muted/30 grid gap-3 rounded-lg border p-3 sm:grid-cols-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                {RATING_FIELDS.map((field) => (
                                    <div
                                        key={field.name}
                                        className="grid gap-1.5"
                                    >
                                        <Label
                                            htmlFor={`${field.name}-${week.id}`}
                                        >
                                            {field.label} (1-5)
                                        </Label>
                                        <Input
                                            id={`${field.name}-${week.id}`}
                                            name={field.name}
                                            type="number"
                                            min={1}
                                            max={5}
                                            defaultValue={
                                                week.rating?.[
                                                    field.name as keyof typeof week.rating
                                                ] as number | undefined
                                            }
                                            required
                                        />
                                        <InputError
                                            message={errors[field.name]}
                                        />
                                    </div>
                                ))}
                                <div className="grid gap-1.5 sm:col-span-2">
                                    <Label htmlFor={`note-${week.id}`}>
                                        Note (optional)
                                    </Label>
                                    <Textarea
                                        id={`note-${week.id}`}
                                        name="note"
                                        rows={2}
                                        defaultValue={week.rating?.note ?? ''}
                                    />
                                    <InputError message={errors.note} />
                                </div>
                                <div className="sm:col-span-2">
                                    <Button
                                        type="submit"
                                        size="sm"
                                        disabled={processing}
                                        data-test={`save-rating-${week.id}`}
                                    >
                                        {week.rating
                                            ? 'Update rating'
                                            : 'Save rating'}
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </CardContent>
        </Card>
    );
}
