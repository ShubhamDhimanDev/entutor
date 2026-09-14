import { Form, Head } from '@inertiajs/react';
import { Clock, UserRound } from 'lucide-react';
import CoachLinkController from '@/actions/App/Http/Controllers/CoachLinkController';
import CoachSettingsController from '@/actions/App/Http/Controllers/Settings/CoachSettingsController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { edit } from '@/routes/coach';

type CoachLink = {
    id: number;
    status: 'pending' | 'active' | 'revoked';
    invite_code: string | null;
    invite_code_expires_at: string | null;
    is_expired: boolean;
    coach_name: string | null;
    coach_email: string | null;
};

export default function Coach({ coachLink }: { coachLink: CoachLink | null }) {
    const state = !coachLink
        ? 'none'
        : coachLink.status === 'active'
          ? 'active'
          : coachLink.is_expired
            ? 'expired'
            : 'pending';

    return (
        <>
            <Head title="Coach" />

            <h1 className="sr-only">Coach settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Coach"
                    description="Link a coach so they can follow your learning progress"
                />

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {state === 'active' && (
                                <UserRound className="size-4" />
                            )}
                            {state === 'pending' && (
                                <Clock className="size-4" />
                            )}
                            {state === 'active' && 'Coach linked'}
                            {state === 'pending' && 'Invite pending'}
                            {state === 'expired' && 'Invite expired'}
                            {state === 'none' && 'No coach linked'}
                        </CardTitle>
                        <CardDescription>
                            {state === 'active' &&
                                'Your coach can see your program and progress.'}
                            {state === 'pending' &&
                                'Share this code with your coach. It expires in 7 days.'}
                            {state === 'expired' &&
                                'Your invite code expired before your coach redeemed it.'}
                            {state === 'none' &&
                                "You don't have a coach linked yet. Generate an invite code and share it with your coach."}
                        </CardDescription>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        {state === 'active' && coachLink && (
                            <div className="flex flex-wrap items-center gap-2">
                                <Badge
                                    variant="outline"
                                    className="border-success/40 bg-success/10 text-success"
                                >
                                    Active
                                </Badge>
                                <span className="text-sm">
                                    {coachLink.coach_name} (
                                    {coachLink.coach_email})
                                </span>
                            </div>
                        )}

                        {state === 'pending' && coachLink && (
                            <div className="flex flex-wrap items-center gap-3">
                                <code
                                    className="bg-muted rounded-md px-3 py-1.5 font-mono text-lg tracking-widest"
                                    data-test="coach-invite-code"
                                >
                                    {coachLink.invite_code}
                                </code>
                                <Badge variant="secondary">Pending</Badge>
                            </div>
                        )}
                    </CardContent>

                    <CardFooter className="gap-3">
                        {(state === 'none' || state === 'expired') && (
                            <Form
                                {...CoachSettingsController.store.form()}
                                options={{ preserveScroll: true }}
                            >
                                {({ processing }) => (
                                    <Button
                                        disabled={processing}
                                        data-test="generate-invite-code-button"
                                    >
                                        Generate invite code
                                    </Button>
                                )}
                            </Form>
                        )}

                        {(state === 'active' || state === 'pending') &&
                            coachLink && (
                                <Dialog>
                                    <DialogTrigger asChild>
                                        <Button
                                            variant="destructive"
                                            data-test="revoke-coach-link-button"
                                        >
                                            {state === 'active'
                                                ? 'Revoke'
                                                : 'Cancel invite'}
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogTitle>
                                            {state === 'active'
                                                ? 'Revoke coach link?'
                                                : 'Cancel this invite?'}
                                        </DialogTitle>
                                        <DialogDescription>
                                            {state === 'active'
                                                ? 'Your coach will no longer be able to see your progress. You can link a new coach afterwards.'
                                                : 'This invite code will stop working. You can generate a new one afterwards.'}
                                        </DialogDescription>
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Never mind
                                                </Button>
                                            </DialogClose>
                                            <Form
                                                {...CoachLinkController.destroy.form(
                                                    { coachLink: coachLink.id },
                                                )}
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        variant="destructive"
                                                        disabled={processing}
                                                        asChild
                                                    >
                                                        <button
                                                            type="submit"
                                                            data-test="confirm-revoke-coach-link-button"
                                                        >
                                                            {state === 'active'
                                                                ? 'Revoke'
                                                                : 'Cancel invite'}
                                                        </button>
                                                    </Button>
                                                )}
                                            </Form>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                            )}
                    </CardFooter>
                </Card>
            </div>
        </>
    );
}

Coach.layout = {
    breadcrumbs: [
        {
            title: 'Coach',
            href: edit(),
        },
    ],
};
