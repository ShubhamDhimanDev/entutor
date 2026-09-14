import type { Transition } from 'motion/react';

/**
 * Named transition presets shared by everything under `@/components/motion`.
 *
 * Guidance (per the redesign's animation spec):
 * - Use the `spring*` presets for interactive UI — buttons, modals, tabs,
 *   hover/tap states. They're physics-based, settle in well under 300ms,
 *   and feel responsive rather than decorative.
 * - Use the `ease*` presets for decorative/sequential motion — page
 *   transitions, staggered list reveals, scroll reveals. They're smooth,
 *   never bounce, and may run 400ms or longer.
 *
 * These are plain `Transition` objects, not components, so callers can
 * spread/override fields (e.g. `{ ...easeStandard, delay: 0.1 }`) as needed.
 */

/** Snappy spring for small interactive elements (buttons, toggles, chips). */
export const springSnappy: Transition = {
    type: 'spring',
    stiffness: 500,
    damping: 32,
    mass: 1,
};

/** Softer spring for larger interactive surfaces (modals, sheets, popovers). */
export const springSoft: Transition = {
    type: 'spring',
    stiffness: 300,
    damping: 28,
    mass: 1,
};

/** Standard easing for decorative/sequential motion (~400ms). */
export const easeStandard: Transition = {
    type: 'tween',
    ease: [0.16, 1, 0.3, 1],
    duration: 0.4,
};

/** Longer easing for more prominent sequential reveals (~600ms). */
export const easeEmphasized: Transition = {
    type: 'tween',
    ease: [0.16, 1, 0.3, 1],
    duration: 0.6,
};

/** Default delay (seconds) between siblings inside a `StaggerContainer`. */
export const STAGGER_GAP = 0.06;
