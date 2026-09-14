import { Head } from '@inertiajs/react';
import { Lightbulb } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import {
    index as roleplayIndex,
    show as roleplayShow,
} from '@/routes/roleplay';
import type { RoleplayScenarioDetail } from '@/types/library';

type Props = {
    scenario: RoleplayScenarioDetail;
};

export default function RoleplayShow({ scenario }: Props) {
    return (
        <>
            <Head title={scenario.title} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center gap-2">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {scenario.title}
                    </h1>
                    <Badge variant="secondary">{scenario.domain_label}</Badge>
                </div>

                <Card className="bg-muted/40">
                    <CardContent className="flex items-start gap-3">
                        <Lightbulb className="text-chart-3 mt-0.5 size-4 shrink-0" />
                        <p className="text-sm">{scenario.tip}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Dialogue</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        {scenario.lines.map((line) => {
                            const isSideA = line.side === 'a';

                            return (
                                <div
                                    key={line.id}
                                    className={cn(
                                        'flex flex-col gap-1',
                                        isSideA ? 'items-start' : 'items-end',
                                    )}
                                    data-test={`roleplay-line-${line.id}`}
                                >
                                    <span className="text-muted-foreground px-1 text-xs font-medium">
                                        {line.speaker_label}
                                    </span>
                                    <div
                                        className={cn(
                                            'max-w-[80%] rounded-2xl px-4 py-2 text-sm',
                                            isSideA
                                                ? 'bg-muted rounded-tl-sm'
                                                : 'bg-primary text-primary-foreground rounded-tr-sm',
                                        )}
                                    >
                                        {line.line_text}
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

RoleplayShow.layout = (props: Props) => ({
    breadcrumbs: [
        {
            title: 'Roleplay Scenarios',
            href: roleplayIndex(),
        },
        {
            title: props.scenario.title,
            href: roleplayShow({ roleplayScenario: props.scenario.id }),
        },
    ],
});
