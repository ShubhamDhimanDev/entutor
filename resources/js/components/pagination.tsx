import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { PaginationLink } from '@/types/library';

/** Decode the small set of HTML entities Laravel's paginator embeds in link labels. */
function decodeLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&hellip;/g, '…');
}

type PaginationProps = {
    links: PaginationLink[];
    className?: string;
};

/**
 * Renders the `links` array from a Laravel paginator (`&laquo; Previous`,
 * page numbers, `Next &raquo;`) as Inertia links, preserving the current
 * query string (search/filter params) since routes call `withQueryString()`.
 */
export function Pagination({ links, className }: PaginationProps) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav
            aria-label="Pagination"
            className={cn(
                'flex flex-wrap items-center justify-center gap-1',
                className,
            )}
        >
            {links.map((link, index) =>
                link.url !== null ? (
                    <Button
                        key={`${link.label}-${index}`}
                        asChild
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        className="min-w-9"
                    >
                        <Link href={link.url} preserveScroll>
                            {decodeLabel(link.label)}
                        </Link>
                    </Button>
                ) : (
                    <Button
                        key={`${link.label}-${index}`}
                        variant="outline"
                        size="sm"
                        disabled
                        className="min-w-9"
                    >
                        {decodeLabel(link.label)}
                    </Button>
                ),
            )}
        </nav>
    );
}
