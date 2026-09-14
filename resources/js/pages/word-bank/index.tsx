import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { PageHeader } from '@/components/page-header';
import { Pagination } from '@/components/pagination';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as wordBankIndex } from '@/routes/word-bank';
import type {
    Paginated,
    WordBankEntry,
    WordBankGroupOption,
} from '@/types/library';

type Props = {
    groups: WordBankGroupOption[];
    filters: {
        q: string | null;
        group: number | null;
    };
    entries: Paginated<WordBankEntry>;
};

const ALL_GROUPS = 'all';

export default function WordBankIndex({ groups, filters, entries }: Props) {
    const [search, setSearch] = useState(filters.q ?? '');

    useEffect(() => {
        const currentQ = filters.q ?? '';

        if (search === currentQ) {
            return;
        }

        const handle = setTimeout(() => {
            router.get(
                wordBankIndex.url({
                    query: {
                        q: search || undefined,
                        group: filters.group ?? undefined,
                    },
                }),
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handle);
        // Only re-run when the debounced search value changes.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    function handleGroupChange(value: string) {
        router.get(
            wordBankIndex.url({
                query: {
                    q: search || undefined,
                    group: value === ALL_GROUPS ? undefined : value,
                },
            }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Word Bank" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="Word Bank"
                    description="Browse vocabulary by theme — meanings, transliteration, and example sentences."
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div className="grid flex-1 gap-2">
                        <Label htmlFor="word-bank-search">Search</Label>
                        <Input
                            id="word-bank-search"
                            type="search"
                            placeholder="Search by term, meaning, or example..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            data-test="word-bank-search"
                        />
                    </div>

                    <div className="grid gap-2 sm:w-56">
                        <Label htmlFor="word-bank-group">Group</Label>
                        <Select
                            value={
                                filters.group !== null
                                    ? String(filters.group)
                                    : ALL_GROUPS
                            }
                            onValueChange={handleGroupChange}
                        >
                            <SelectTrigger
                                id="word-bank-group"
                                className="w-full"
                                data-test="word-bank-group-filter"
                            >
                                <SelectValue placeholder="All groups" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL_GROUPS}>
                                    All groups
                                </SelectItem>
                                {groups.map((group) => (
                                    <SelectItem
                                        key={group.id}
                                        value={String(group.id)}
                                    >
                                        {group.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {entries.data.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-10 text-center text-sm">
                            No words match your search.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-3">
                        {entries.data.map((entry) => (
                            <Card
                                key={entry.id}
                                data-test={`word-bank-entry-${entry.id}`}
                            >
                                <CardContent className="grid gap-1.5">
                                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                                        <p className="text-base font-semibold">
                                            {entry.term}
                                        </p>
                                        <span className="text-muted-foreground text-xs font-medium tracking-wide uppercase">
                                            {entry.group.name}
                                        </span>
                                    </div>
                                    <p className="text-sm">
                                        {entry.native_meaning}{' '}
                                        <span className="text-muted-foreground">
                                            ({entry.native_transliteration})
                                        </span>
                                    </p>
                                    <p className="text-muted-foreground text-sm italic">
                                        “{entry.example_sentence}”
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                <Pagination links={entries.links} />
            </div>
        </>
    );
}

WordBankIndex.layout = {
    breadcrumbs: [
        {
            title: 'Word Bank',
            href: wordBankIndex(),
        },
    ],
};
