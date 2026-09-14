import { Head, Link } from '@inertiajs/react';
import { StaggerContainer, StaggerItem } from '@/components/motion/stagger';
import { PageHeader } from '@/components/page-header';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useInitials } from '@/hooks/use-initials';
import { index, show } from '@/routes/coach/learners';
import { create as redeemCreate } from '@/routes/coach/redeem';

type Learner = {
    id: number;
    name: string;
    email: string;
    start_date: string;
};

export default function LearnersIndex({ learners }: { learners: Learner[] }) {
    const getInitials = useInitials();

    return (
        <>
            <Head title="My learners" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <PageHeader
                    title="My learners"
                    description="Learners who have linked you as their coach."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={redeemCreate()}>Link a learner</Link>
                        </Button>
                    }
                />

                {learners.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-10 text-center text-sm">
                            No learners yet. When a learner shares an invite
                            code with you, redeem it to start following their
                            progress here.
                        </CardContent>
                    </Card>
                ) : (
                    <StaggerContainer className="grid gap-3" gap={0.04}>
                        {learners.map((learner) => (
                            <StaggerItem key={learner.id}>
                                <Link
                                    href={show({
                                        learnerProgram: learner.id,
                                    })}
                                    data-test={`learner-row-${learner.id}`}
                                >
                                    <Card className="hover:bg-muted/50 transition-[background-color,box-shadow] hover:shadow-md">
                                        <CardContent className="flex items-center gap-4">
                                            <Avatar className="size-10">
                                                <AvatarFallback className="bg-secondary text-secondary-foreground">
                                                    {getInitials(learner.name)}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div className="min-w-0 flex-1">
                                                <p className="truncate font-medium">
                                                    {learner.name}
                                                </p>
                                                <p className="text-muted-foreground truncate text-sm">
                                                    {learner.email}
                                                </p>
                                            </div>
                                            <p className="text-muted-foreground text-sm whitespace-nowrap">
                                                Started {learner.start_date}
                                            </p>
                                        </CardContent>
                                    </Card>
                                </Link>
                            </StaggerItem>
                        ))}
                    </StaggerContainer>
                )}
            </div>
        </>
    );
}

LearnersIndex.layout = {
    breadcrumbs: [
        {
            title: 'My learners',
            href: index(),
        },
    ],
};
