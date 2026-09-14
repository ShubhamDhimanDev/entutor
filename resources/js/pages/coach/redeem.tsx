import { Form, Head } from '@inertiajs/react';
import { Link2 } from 'lucide-react';
import CoachLinkController from '@/actions/App/Http/Controllers/CoachLinkController';
import InputError from '@/components/input-error';
import { FadeIn } from '@/components/motion/fade-in';
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
import { create } from '@/routes/coach/redeem';

export default function Redeem() {
    return (
        <>
            <Head title="Link a learner" />

            <div className="flex flex-1 items-start justify-center p-4 pt-12">
                <FadeIn className="w-full max-w-md">
                    <Card className="w-full">
                        <CardHeader>
                            <div className="bg-primary/10 text-primary mx-auto flex size-10 items-center justify-center rounded-lg">
                                <Link2 className="size-5" />
                            </div>
                            <CardTitle>Link a learner</CardTitle>
                            <CardDescription>
                                Enter the invite code your learner shared with
                                you to start following their progress.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...CoachLinkController.store.form()}
                                resetOnSuccess
                                className="space-y-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="code">
                                                Invite code
                                            </Label>
                                            <Input
                                                id="code"
                                                name="code"
                                                required
                                                autoFocus
                                                autoComplete="off"
                                                placeholder="e.g. AB12CD34"
                                                className="uppercase"
                                            />
                                            <InputError message={errors.code} />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={processing}
                                            data-test="redeem-invite-code-button"
                                        >
                                            Link learner
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </FadeIn>
            </div>
        </>
    );
}

Redeem.layout = {
    breadcrumbs: [
        {
            title: 'Link a learner',
            href: create(),
        },
    ],
};
