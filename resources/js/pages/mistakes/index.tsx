import { Head, router } from '@inertiajs/react';
import { PageHeader } from '@/components/page-header';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as mistakesIndex } from '@/routes/mistakes';
import type {
    CommonMistake,
    MistakeTagOption,
    Paginated,
} from '@/types/library';

type Props = {
    tags: MistakeTagOption[];
    filters: {
        tag: string | null;
    };
    mistakes: Paginated<CommonMistake>;
};

export default function MistakesIndex({ tags, filters, mistakes }: Props) {
    function handleTagChange(tag: string | null) {
        router.get(
            mistakesIndex.url({ query: { tag: tag ?? undefined } }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Fix Common Mistakes" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="Fix Common Mistakes"
                    description="Frequent mistakes learners make, corrected and explained."
                />

                <div
                    className="flex flex-wrap gap-2"
                    role="group"
                    aria-label="Filter by mistake tag"
                >
                    <Button
                        type="button"
                        variant={filters.tag === null ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => handleTagChange(null)}
                    >
                        All
                    </Button>
                    {tags.map((tag) => (
                        <Button
                            key={tag.value}
                            type="button"
                            variant={
                                filters.tag === tag.value
                                    ? 'default'
                                    : 'outline'
                            }
                            size="sm"
                            onClick={() => handleTagChange(tag.value)}
                            data-test={`mistake-tag-${tag.value}`}
                        >
                            {tag.label}
                        </Button>
                    ))}
                </div>

                {mistakes.data.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-10 text-center text-sm">
                            No mistakes found for this tag.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-3">
                        {mistakes.data.map((mistake) => (
                            <Card
                                key={mistake.id}
                                data-test={`mistake-${mistake.id}`}
                            >
                                <CardContent className="grid gap-2">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-destructive text-sm line-through decoration-2">
                                            {mistake.wrong_sentence}
                                        </p>
                                        <Badge
                                            variant="outline"
                                            className="shrink-0"
                                        >
                                            {
                                                tags.find(
                                                    (t) =>
                                                        t.value === mistake.tag,
                                                )?.label
                                            }
                                        </Badge>
                                    </div>
                                    <p className="text-success text-sm font-medium">
                                        {mistake.corrected_sentence}
                                    </p>
                                    <p className="text-muted-foreground text-sm">
                                        {mistake.explanation}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                <Pagination links={mistakes.links} />
            </div>
        </>
    );
}

MistakesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Fix Common Mistakes',
            href: mistakesIndex(),
        },
    ],
};
