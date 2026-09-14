import { Head, Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Bot,
    CalendarCheck,
    ClipboardCheck,
    Drama,
    MessageCircleQuestion,
    Sparkles,
    SpellCheck,
    Users,
} from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { FadeIn } from '@/components/motion/fade-in';
import { StaggerContainer, StaggerItem } from '@/components/motion/stagger';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard, home, login, register } from '@/routes';

const features = [
    {
        icon: CalendarCheck,
        title: 'A 180-day curriculum',
        description:
            'Months, weeks, and days broken into reading, vocabulary, listening, speaking, and writing tasks, each with an estimated time to complete.',
    },
    {
        icon: ClipboardCheck,
        title: 'Weekly ratings, monthly tests',
        description:
            'Reading, speaking, listening, and confidence are rated every week. Each month ends with a test made of skill-weighted sections.',
    },
    {
        icon: Users,
        title: 'A coach in your corner',
        description:
            'Invite a practice partner with an invite code. They can review your progress and log ratings or test scores on your behalf.',
    },
    {
        icon: Sparkles,
        title: 'A practice library, not just lessons',
        description:
            'Word Bank vocabulary, tagged common-mistake fixes, roleplay scenarios, AI conversation prompts, and confidence Q&A drills back every day of the plan.',
    },
];

const library = [
    {
        icon: BookOpen,
        title: 'Word Bank',
        description:
            'Vocabulary organized into groups, each entry with a definition and an example.',
    },
    {
        icon: SpellCheck,
        title: 'Common Mistakes',
        description:
            'Corrections tagged by type: tense, articles, prepositions, direct translation, grammar basics, vocabulary, pronunciation.',
    },
    {
        icon: Drama,
        title: 'Roleplay Scenarios',
        description:
            'Scripted conversations across five situations: phone messages, shopping & money, health & everyday help, problems & complaints, social & family.',
    },
    {
        icon: Bot,
        title: 'AI Prompts',
        description:
            'Categorized prompts for practicing conversation with an AI chat tool of your choice.',
    },
    {
        icon: MessageCircleQuestion,
        title: 'Confidence Q&A',
        description:
            'Question sets grouped by topic, for practicing spoken answers out loud.',
    },
];

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="bg-background flex min-h-screen flex-col">
                <header className="flex items-center justify-between px-6 py-4 md:px-10">
                    <Link
                        href={home()}
                        className="text-foreground flex items-center gap-2"
                    >
                        <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-md">
                            <AppLogoIcon className="size-5 fill-current" />
                        </div>
                        <span className="text-sm font-semibold">{name}</span>
                    </Link>

                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <Button asChild>
                                <Link href={dashboard()}>Go to dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Log in</Link>
                                </Button>
                                <Button asChild>
                                    <Link href={register()}>Get started</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </header>

                <section className="relative flex flex-col items-center justify-center gap-6 overflow-hidden px-6 py-24 text-center md:py-32">
                    <div className="bg-primary/20 dark:bg-primary/10 absolute top-0 left-1/2 -z-10 h-[480px] w-[480px] -translate-x-1/2 rounded-full blur-3xl" />

                    <StaggerContainer
                        gap={0.08}
                        className="flex flex-col items-center gap-6"
                    >
                        <StaggerItem>
                            <Badge
                                variant="outline"
                                className="border-chart-3/40 bg-chart-3/10 text-chart-3"
                            >
                                180-day program, structured day by day
                            </Badge>
                        </StaggerItem>

                        <StaggerItem>
                            <h1 className="max-w-3xl text-4xl leading-[1.05] font-bold tracking-tight md:text-5xl lg:text-6xl">
                                Speak English with{' '}
                                <span className="from-primary via-chart-3 to-primary animate-[gradient-pan_6s_ease-in-out_infinite] bg-gradient-to-r bg-[length:200%_auto] bg-clip-text text-transparent">
                                    confidence
                                </span>
                                , one day at a time.
                            </h1>
                        </StaggerItem>

                        <StaggerItem>
                            <p className="text-muted-foreground max-w-xl text-lg">
                                {name} breaks 180 days of spoken-English
                                practice into daily reading, vocabulary,
                                listening, speaking, and writing tasks — tracked
                                week by week, tested month by month, with an
                                optional coach to review your progress.
                            </p>
                        </StaggerItem>

                        <StaggerItem>
                            <div className="flex items-center gap-3">
                                {auth.user ? (
                                    <Button size="lg" asChild>
                                        <Link href={dashboard()}>
                                            Go to dashboard
                                        </Link>
                                    </Button>
                                ) : (
                                    <>
                                        <Button size="lg" asChild>
                                            <Link href={register()}>
                                                Get started
                                            </Link>
                                        </Button>
                                        <Button
                                            size="lg"
                                            variant="ghost"
                                            asChild
                                        >
                                            <Link href={login()}>Log in</Link>
                                        </Button>
                                    </>
                                )}
                            </div>
                        </StaggerItem>
                    </StaggerContainer>
                </section>

                <StaggerContainer
                    gap={0.06}
                    className="grid gap-6 px-6 py-16 md:grid-cols-2 md:px-10 lg:grid-cols-4"
                >
                    {features.map((feature) => (
                        <StaggerItem key={feature.title}>
                            <Card className="h-full">
                                <CardContent className="flex flex-col gap-3">
                                    <div className="bg-primary/10 text-primary flex size-10 items-center justify-center rounded-lg">
                                        <feature.icon className="size-5" />
                                    </div>
                                    <h2 className="text-base font-semibold">
                                        {feature.title}
                                    </h2>
                                    <p className="text-muted-foreground text-sm">
                                        {feature.description}
                                    </p>
                                </CardContent>
                            </Card>
                        </StaggerItem>
                    ))}
                </StaggerContainer>

                <section className="border-t px-6 py-16 md:px-10">
                    <FadeIn className="mx-auto max-w-2xl text-center">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            Inside the practice library
                        </h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            The same word bank, mistake fixes, roleplay scripts,
                            AI prompts, and confidence drills back every day of
                            the curriculum.
                        </p>
                    </FadeIn>

                    <StaggerContainer
                        gap={0.06}
                        className="mx-auto mt-10 grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-5"
                    >
                        {library.map((item) => (
                            <StaggerItem key={item.title}>
                                <div className="flex h-full flex-col gap-3 text-left">
                                    <div className="bg-chart-3/10 text-chart-3 flex size-10 items-center justify-center rounded-lg">
                                        <item.icon className="size-5" />
                                    </div>
                                    <h3 className="text-sm font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="text-muted-foreground text-sm">
                                        {item.description}
                                    </p>
                                </div>
                            </StaggerItem>
                        ))}
                    </StaggerContainer>
                </section>

                <FadeIn className="text-muted-foreground border-t px-6 py-8 text-center text-sm">
                    {name} &middot;{' '}
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="hover:text-foreground underline underline-offset-4"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={login()}
                                className="hover:text-foreground underline underline-offset-4"
                            >
                                Log in
                            </Link>{' '}
                            &middot;{' '}
                            <Link
                                href={register()}
                                className="hover:text-foreground underline underline-offset-4"
                            >
                                Register
                            </Link>
                        </>
                    )}
                </FadeIn>
            </div>
        </>
    );
}
