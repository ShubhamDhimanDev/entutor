import { motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';
import { easeStandard } from '@/lib/motion';
import { cn } from '@/lib/utils';

type FadeInProps = {
    children: ReactNode;
    className?: string;
    /** Delay (seconds) before the fade starts. Defaults to 0. */
    delay?: number;
    /** Vertical offset (px) the element travels in from. Defaults to 8. */
    distance?: number;
};

/**
 * Fades (and gently rises) a single element in on mount. For lists/grids of
 * elements, prefer `StaggerContainer`/`StaggerItem` instead so entrances are
 * orchestrated rather than simultaneous.
 */
export function FadeIn({
    children,
    className,
    delay = 0,
    distance = 8,
}: FadeInProps) {
    const shouldReduceMotion = useReducedMotion();

    return (
        <motion.div
            className={cn(className)}
            initial={{ opacity: 0, y: shouldReduceMotion ? 0 : distance }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ ...easeStandard, delay }}
        >
            {children}
        </motion.div>
    );
}
