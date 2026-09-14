import { Link, usePage } from '@inertiajs/react';
import {
    BookOpenText,
    History,
    LayoutGrid,
    MessageCircleQuestion,
    MessagesSquare,
    Sparkles,
    SpellCheck2,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as aiPromptsIndex } from '@/routes/ai-prompts';
import { index as coachLearners } from '@/routes/coach/learners';
import { index as confidenceQaIndex } from '@/routes/confidence-qa';
import { index as mistakesIndex } from '@/routes/mistakes';
import { index as roleplayIndex } from '@/routes/roleplay';
import { index as tensesIndex } from '@/routes/tenses';
import { index as wordBankIndex } from '@/routes/word-bank';
import type { NavItem } from '@/types';

const learnerNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const coachNavItems: NavItem[] = [
    {
        title: 'My learners',
        href: coachLearners(),
        icon: Users,
    },
];

// Content-library browsing pages — global reference content, identical for
// every user regardless of role, so this list is shared by both nav sets.
const libraryNavItems: NavItem[] = [
    {
        title: 'Word Bank',
        href: wordBankIndex(),
        icon: BookOpenText,
    },
    {
        title: 'Tenses',
        href: tensesIndex(),
        icon: History,
    },
    {
        title: 'Fix Common Mistakes',
        href: mistakesIndex(),
        icon: SpellCheck2,
    },
    {
        title: 'Roleplay Scenarios',
        href: roleplayIndex(),
        icon: MessagesSquare,
    },
    {
        title: 'AI Practice Prompts',
        href: aiPromptsIndex(),
        icon: Sparkles,
    },
    {
        title: 'Confidence Q&A',
        href: confidenceQaIndex(),
        icon: MessageCircleQuestion,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props;
    const mainNavItems =
        auth.user.role === 'coach' ? coachNavItems : learnerNavItems;
    const homeHref = auth.user.role === 'coach' ? coachLearners() : dashboard();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={homeHref} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                <NavMain items={libraryNavItems} label="Library" />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
