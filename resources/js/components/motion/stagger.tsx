import { motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';
import { STAGGER_GAP, easeStandard } from '@/lib/motion';
import { cn } from '@/lib/utils';

type StaggerContainerProps = {
    children: ReactNode;
    className?: string;
    /** Seconds between each direct `StaggerItem` child animating in. */
    gap?: number;
    /** Delay (seconds) before the first child starts. Defaults to 0. */
    delay?: number;
};

/**
 * Orchestrates entrance animations for a list/grid of `StaggerItem`
 * children (e.g. dashboard stat cards, curriculum lists). `StaggerItem`s
 * don't need to be direct children — Motion propagates `variants` down the
 * React tree — but nothing between them should set its own `animate` prop,
 * which would break propagation.
 */
export function StaggerContainer({
    children,
    className,
    gap = STAGGER_GAP,
    delay = 0,
}: StaggerContainerProps) {
    const shouldReduceMotion = useReducedMotion();

    return (
        <motion.div
            className={cn(className)}
            initial="hidden"
            animate="visible"
            variants={{
                hidden: {},
                visible: {
                    transition: {
                        delayChildren: delay,
                        staggerChildren: shouldReduceMotion ? 0 : gap,
                    },
                },
            }}
        >
            {children}
        </motion.div>
    );
}

type StaggerItemProps = {
    children: ReactNode;
    className?: string;
    /** Vertical offset (px) the item travels in from. Defaults to 12. */
    distance?: number;
    /**
     * Explicit position in the sequence. Only needed when this item can't
     * rely on a parent `StaggerContainer`'s `staggerChildren` orchestration
     * (e.g. it's rendered outside one, or through a boundary that resets
     * variant context) — providing it computes the item's own entrance
     * delay directly instead.
     */
    index?: number;
};

/** One animated entry inside a `StaggerContainer`. See that component's docs. */
export function StaggerItem({
    children,
    className,
    distance = 12,
    index,
}: StaggerItemProps) {
    const shouldReduceMotion = useReducedMotion();
    const hiddenY = shouldReduceMotion ? 0 : distance;

    return (
        <motion.div
            className={cn(className)}
            variants={{
                hidden: { opacity: 0, y: hiddenY },
                visible: {
                    opacity: 1,
                    y: 0,
                    transition:
                        index === undefined
                            ? easeStandard
                            : { ...easeStandard, delay: index * STAGGER_GAP },
                },
            }}
        >
            {children}
        </motion.div>
    );
}
