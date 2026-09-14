import { Head } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { index as confidenceQaIndex } from '@/routes/confidence-qa';
import type { ConfidenceTopic } from '@/types/library';

type Props = {
    topics: ConfidenceTopic[];
};

export default function ConfidenceQaIndex({ topics }: Props) {
    return (
        <>
            <Head title="Confidence Q&A" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Confidence Q&A
                    </h1>
                    <p className="text-muted-foreground text-sm">
                        Common questions you might be asked — expand a question
                        to see an example answer.
                    </p>
                </div>

                {topics.map((topic) => (
                    <section key={topic.id} className="grid gap-3">
                        <h2 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                            {topic.name}
                        </h2>

                        <div className="grid gap-2">
                            {topic.questions.map((question) => (
                                <Collapsible
                                    key={question.id}
                                    className="rounded-lg border"
                                    data-test={`confidence-question-${question.id}`}
                                >
                                    <CollapsibleTrigger className="group flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium">
                                        {question.question}
                                        <ChevronDown className="text-muted-foreground size-4 shrink-0 transition-transform duration-200 group-data-[state=open]:rotate-180" />
                                    </CollapsibleTrigger>
                                    <CollapsibleContent className="text-muted-foreground border-t px-4 py-3 text-sm">
                                        {question.example_answer}
                                    </CollapsibleContent>
                                </Collapsible>
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </>
    );
}

ConfidenceQaIndex.layout = {
    breadcrumbs: [
        {
            title: 'Confidence Q&A',
            href: confidenceQaIndex(),
        },
    ],
};
