import { Link } from '@inertiajs/react';
import { show as showDay } from '@/routes/days';
import { cn } from '@/lib/utils';
import type { DaySummary } from '@/types';

export function DayGrid({ days }: { days: DaySummary[] }) {
    if (days.length === 0) {
        return (
            <p className="text-muted-foreground text-sm">No days seeded yet.</p>
        );
    }

    return (
        <div className="grid grid-cols-10 gap-1.5 sm:grid-cols-15 md:grid-cols-18 lg:grid-cols-20">
            {days.map((day) => (
                <Link
                    key={day.id}
                    href={showDay(day.id)}
                    title={`Day ${day.day_number}: ${day.title}${day.done ? ' (complete)' : ''}`}
                    data-test={`day-grid-cell-${day.day_number}`}
                    className={cn(
                        'flex size-7 items-center justify-center rounded-md text-[10px] font-medium transition-colors hover:scale-105',
                        day.done
                            ? 'bg-success text-success-foreground hover:bg-success/90'
                            : 'bg-muted text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    )}
                >
                    {day.day_number}
                </Link>
            ))}
        </div>
    );
}
