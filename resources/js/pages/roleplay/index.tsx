import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    index as roleplayIndex,
    show as roleplayShow,
} from '@/routes/roleplay';
import type {
    RoleplayDomainOption,
    RoleplayScenarioSummary,
} from '@/types/library';

type Props = {
    domains: RoleplayDomainOption[];
    filters: {
        domain: string | null;
    };
    scenarios: RoleplayScenarioSummary[];
};

export default function RoleplayIndex({ domains, filters, scenarios }: Props) {
    function handleDomainChange(domain: string | null) {
        router.get(
            roleplayIndex.url({ query: { domain: domain ?? undefined } }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Roleplay Scenarios" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="Roleplay Scenarios"
                    description="Practice real-life conversations, scripted line by line."
                />

                <div
                    className="flex flex-wrap gap-2"
                    role="group"
                    aria-label="Filter by domain"
                >
                    <Button
                        type="button"
                        variant={
                            filters.domain === null ? 'default' : 'outline'
                        }
                        size="sm"
                        onClick={() => handleDomainChange(null)}
                    >
                        All
                    </Button>
                    {domains.map((domain) => (
                        <Button
                            key={domain.value}
                            type="button"
                            variant={
                                filters.domain === domain.value
                                    ? 'default'
                                    : 'outline'
                            }
                            size="sm"
                            onClick={() => handleDomainChange(domain.value)}
                            data-test={`roleplay-domain-${domain.value}`}
                        >
                            {domain.label}
                        </Button>
                    ))}
                </div>

                {scenarios.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-10 text-center text-sm">
                            No scenarios found for this domain.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-3">
                        {scenarios.map((scenario) => (
                            <Link
                                key={scenario.id}
                                href={roleplayShow({
                                    roleplayScenario: scenario.id,
                                })}
                                data-test={`roleplay-scenario-${scenario.id}`}
                            >
                                <Card className="hover:bg-muted/50 transition-colors">
                                    <CardContent className="flex items-center gap-4">
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <p className="font-medium">
                                                    {scenario.title}
                                                </p>
                                                <Badge variant="secondary">
                                                    {
                                                        domains.find(
                                                            (d) =>
                                                                d.value ===
                                                                scenario.domain,
                                                        )?.label
                                                    }
                                                </Badge>
                                            </div>
                                            <p className="text-muted-foreground truncate text-sm">
                                                {scenario.tip_preview}
                                            </p>
                                        </div>
                                        <ArrowRight className="text-muted-foreground size-4 shrink-0" />
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

RoleplayIndex.layout = {
    breadcrumbs: [
        {
            title: 'Roleplay Scenarios',
            href: roleplayIndex(),
        },
    ],
};
