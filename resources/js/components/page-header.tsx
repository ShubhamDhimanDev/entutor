import type { ReactNode } from 'react';

type PageHeaderProps = {
    title: string;
    description?: string;
    actions?: ReactNode;
};

/**
 * Standard top-of-page heading used across authenticated pages: a title
 * (+ optional description) on the left, an optional right-aligned actions
 * slot (buttons, search bars, etc.), stacked on mobile and side-by-side from
 * `sm:` up. Keep the props exactly `{ title, description, actions }` — this
 * is imported across dashboard, coach, and content-library pages.
 */
export function PageHeader({ title, description, actions }: PageHeaderProps) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 className="text-2xl font-semibold tracking-tight">
                    {title}
                </h1>
                {description && (
                    <p className="text-muted-foreground text-sm">
                        {description}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex items-center gap-2">{actions}</div>
            )}
        </div>
    );
}
