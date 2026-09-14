import { usePage } from '@inertiajs/react';
import { AnimatePresence, motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';
import { easeStandard } from '@/lib/motion';
import { cn } from '@/lib/utils';

type PageTransitionProps = {
    children: ReactNode;
    className?: string;
    /**
     * Overrides the key used to detect a "new page" and trigger a
     * transition. Defaults to the current Inertia visit URL
     * (`usePage().url`, including its query string). Pass a coarser value
     * (e.g. just the pathname) if query-string-only navigations on the same
     * page shouldn't replay the transition.
     */
    transitionKey?: string;
};

/**
 * Wraps the content a layout renders so Inertia navigations fade/slide
 * between pages instead of swapping abruptly. Intended for use inside
 * `AppLayout`/`AuthLayout`/`SettingsLayout` (wrapping their `children`), not
 * as a replacement for those layouts.
 *
 * Uses `mode="wait"` so the outgoing page finishes its exit animation before
 * the incoming one enters, avoiding two full pages overlapping on screen.
 */
export function PageTransition({
    children,
    className,
    transitionKey,
}: PageTransitionProps) {
    const { url } = usePage();
    const shouldReduceMotion = useReducedMotion();
    const key = transitionKey ?? url;
    const distance = shouldReduceMotion ? 0 : 8;

    return (
        <AnimatePresence mode="wait" initial={false}>
            <motion.div
                key={key}
                className={cn(className)}
                initial={{ opacity: 0, y: distance }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -distance }}
                transition={easeStandard}
            >
                {children}
            </motion.div>
        </AnimatePresence>
    );
}
