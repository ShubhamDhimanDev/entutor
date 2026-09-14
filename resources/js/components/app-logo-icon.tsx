import type { SVGAttributes } from 'react';

/**
 * EnTutor mark — a geometric "E" lettermark built from solid, non-overlapping
 * rectangles (spine + top/middle/bottom bars) so it stays crisp and legible
 * at small icon sizes (size-5/8/9). Monochrome/`currentColor`, no `fill` set
 * on the shapes themselves so it inherits color from the `fill-current`
 * utility applied at call sites.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            {/* Spine */}
            <rect x="4" y="3" width="4" height="18" rx="1" />
            {/* Top bar */}
            <rect x="8" y="3" width="10" height="2.57" rx="0.5" />
            {/* Middle bar */}
            <rect x="8" y="10.71" width="8" height="2.57" rx="0.5" />
            {/* Bottom bar */}
            <rect x="8" y="18.43" width="10" height="2.57" rx="0.5" />
        </svg>
    );
}
