import { Head } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as tensesIndex, show as tensesShow } from '@/routes/tenses';
import type { TenseDetail } from '@/types/library';

type Props = {
    tense: TenseDetail;
};

const TIME_LABEL: Record<TenseDetail['time'], string> = {
    present: 'Present',
    past: 'Past',
    future: 'Future',
};

const ASPECT_LABEL: Record<TenseDetail['aspect'], string> = {
    simple: 'Simple',
    continuous: 'Continuous',
    perfect: 'Perfect',
    perfect_continuous: 'Perfect Continuous',
};

export default function TenseShow({ tense }: Props) {
    return (
        <>
            <Head title={tense.name} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center gap-2">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {tense.name}
                    </h1>
                    <Badge variant="secondary">{TIME_LABEL[tense.time]}</Badge>
                    <Badge variant="outline">
                        {ASPECT_LABEL[tense.aspect]}
                    </Badge>
                </div>
                <p className="text-muted-foreground text-sm">{tense.summary}</p>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Structure</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="sm:divide-border grid gap-6 sm:grid-cols-3 sm:divide-x">
                            <div className="sm:px-4 sm:first:pl-0 sm:last:pr-0">
                                <dt className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
                                    Affirmative
                                </dt>
                                <dd className="mt-1 text-sm font-medium">
                                    {tense.structure_affirmative}
                                </dd>
                            </div>
                            <div className="sm:px-4 sm:first:pl-0 sm:last:pr-0">
                                <dt className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
                                    Negative
                                </dt>
                                <dd className="mt-1 text-sm font-medium">
                                    {tense.structure_negative}
                                </dd>
                            </div>
                            <div className="sm:px-4 sm:first:pl-0 sm:last:pr-0">
                                <dt className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
                                    Interrogative
                                </dt>
                                <dd className="mt-1 text-sm font-medium">
                                    {tense.structure_interrogative}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Usage rules</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul className="list-disc space-y-1.5 pl-5 text-sm">
                            {tense.usage_rules.map((rule) => (
                                <li key={rule}>{rule}</li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>

                {tense.signal_words.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                Signal words
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-1.5">
                                {tense.signal_words.map((word) => (
                                    <Badge key={word} variant="outline">
                                        {word}
                                    </Badge>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {tense.common_confusions && (
                    <Alert data-test="tense-common-confusions">
                        <AlertTriangle className="text-chart-3" />
                        <AlertTitle>Don&apos;t confuse this with…</AlertTitle>
                        <AlertDescription>
                            {tense.common_confusions}
                        </AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Examples</CardTitle>
                    </CardHeader>
                    <CardContent className="divide-border divide-y">
                        {tense.examples.map((example, index) => (
                            <div
                                key={index}
                                className="grid gap-0.5 py-3 first:pt-0 last:pb-0"
                            >
                                <p className="text-sm">{example.sentence}</p>
                                <p className="text-muted-foreground text-sm">
                                    {example.native_meaning}
                                </p>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

TenseShow.layout = (props: Props) => ({
    breadcrumbs: [
        { title: 'Tenses', href: tensesIndex() },
        {
            title: props.tense.name,
            href: tensesShow({ tense: props.tense.key }),
        },
    ],
});
