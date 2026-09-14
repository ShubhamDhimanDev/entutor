import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as tensesIndex, show as tensesShow } from '@/routes/tenses';
import type { TenseSummary, TenseTimeOption } from '@/types/library';

type Props = {
    times: TenseTimeOption[];
    filters: {
        time: string | null;
    };
    tenses: TenseSummary[];
};

const ASPECT_LABEL: Record<TenseSummary['aspect'], string> = {
    simple: 'Simple',
    continuous: 'Continuous',
    perfect: 'Perfect',
    perfect_continuous: 'Perfect Continuous',
};

const TIME_SECTIONS: Array<{ value: TenseSummary['time']; label: string }> = [
    { value: 'present', label: 'Present' },
    { value: 'past', label: 'Past' },
    { value: 'future', label: 'Future' },
];

function TenseCard({ tense }: { tense: TenseSummary }) {
    return (
        <Link
            href={tensesShow({ tense: tense.key })}
            data-test={`tense-${tense.id}`}
        >
            <Card className="hover:bg-muted/50 transition-colors">
                <CardContent className="flex items-center gap-4">
                    <div className="min-w-0 flex-1 space-y-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <p className="font-medium">{tense.name}</p>
                            <Badge variant="secondary">
                                {ASPECT_LABEL[tense.aspect]}
                            </Badge>
                        </div>
                        <p className="text-muted-foreground truncate text-sm">
                            {tense.summary}
                        </p>
                    </div>
                    <ArrowRight className="text-muted-foreground size-4 shrink-0" />
                </CardContent>
            </Card>
        </Link>
    );
}

export default function TensesIndex({ times, filters, tenses }: Props) {
    function handleTimeChange(time: string | null) {
        router.get(
            tensesIndex.url({ query: { time: time ?? undefined } }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Tenses" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="Tenses"
                    description="The 12 core English tenses — structure, usage, and examples."
                />

                <div
                    className="flex flex-wrap gap-2"
                    role="group"
                    aria-label="Filter by time"
                >
                    <Button
                        type="button"
                        variant={filters.time === null ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => handleTimeChange(null)}
                    >
                        All
                    </Button>
                    {times.map((time) => (
                        <Button
                            key={time.value}
                            type="button"
                            variant={
                                filters.time === time.value
                                    ? 'default'
                                    : 'outline'
                            }
                            size="sm"
                            onClick={() => handleTimeChange(time.value)}
                            data-test={`tense-time-${time.value}`}
                        >
                            {time.label}
                        </Button>
                    ))}
                </div>

                {tenses.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-10 text-center text-sm">
                            No tenses found for this time.
                        </CardContent>
                    </Card>
                ) : filters.time === null ? (
                    <>
                        {TIME_SECTIONS.map((section) => {
                            const sectionTenses = tenses.filter(
                                (tense) => tense.time === section.value,
                            );

                            if (sectionTenses.length === 0) {
                                return null;
                            }

                            return (
                                <section
                                    key={section.value}
                                    className="grid gap-3"
                                >
                                    <h2 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                                        {section.label}
                                    </h2>

                                    <div className="grid gap-3">
                                        {sectionTenses.map((tense) => (
                                            <TenseCard
                                                key={tense.id}
                                                tense={tense}
                                            />
                                        ))}
                                    </div>
                                </section>
                            );
                        })}
                    </>
                ) : (
                    <div className="grid gap-3">
                        {tenses.map((tense) => (
                            <TenseCard key={tense.id} tense={tense} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

TensesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Tenses',
            href: tensesIndex(),
        },
    ],
};
