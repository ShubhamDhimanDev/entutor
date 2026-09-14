import { Head } from '@inertiajs/react';
import { Check, Copy } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useClipboard } from '@/hooks/use-clipboard';
import { index as aiPromptsIndex } from '@/routes/ai-prompts';
import type { AiPromptCategory } from '@/types/library';

type Props = {
    categories: AiPromptCategory[];
};

export default function AiPromptsIndex({ categories }: Props) {
    const [, copy] = useClipboard();
    const [copiedId, setCopiedId] = useState<number | null>(null);

    async function handleCopy(id: number, text: string) {
        const copied = await copy(text);

        if (copied) {
            setCopiedId(id);
            setTimeout(
                () =>
                    setCopiedId((current) => (current === id ? null : current)),
                1500,
            );
        }
    }

    return (
        <>
            <Head title="AI Practice Prompts" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        AI Practice Prompts
                    </h1>
                    <p className="text-muted-foreground text-sm">
                        Copy a prompt into your favorite AI chat tool to
                        practice speaking and writing English.
                    </p>
                </div>

                {categories.map((category) => (
                    <section key={category.id} className="grid gap-3">
                        <h2 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                            {category.name}
                        </h2>

                        <div className="grid gap-3">
                            {category.prompts.map((prompt) => (
                                <Card
                                    key={prompt.id}
                                    data-test={`ai-prompt-${prompt.id}`}
                                >
                                    <CardContent className="flex items-start justify-between gap-3">
                                        <p className="text-sm">
                                            {prompt.prompt_text}
                                        </p>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className="shrink-0"
                                            onClick={() =>
                                                handleCopy(
                                                    prompt.id,
                                                    prompt.prompt_text,
                                                )
                                            }
                                        >
                                            {copiedId === prompt.id ? (
                                                <>
                                                    <Check /> Copied
                                                </>
                                            ) : (
                                                <>
                                                    <Copy /> Copy
                                                </>
                                            )}
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </>
    );
}

AiPromptsIndex.layout = {
    breadcrumbs: [
        {
            title: 'AI Practice Prompts',
            href: aiPromptsIndex(),
        },
    ],
};
