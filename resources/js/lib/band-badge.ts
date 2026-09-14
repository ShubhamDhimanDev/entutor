/**
 * Maps a `TestScoreBand` enum value (`app/Enums/TestScoreBand.php`) to
 * `Badge` outline-variant classes tinted by result quality. Used alongside
 * `<Badge variant="outline" className={getBandBadgeClassName(band)}>`.
 *
 * Falls back to neutral/secondary-equivalent classes for any unrecognized
 * value so it degrades safely instead of throwing.
 */
export function getBandBadgeClassName(band: string): string {
    switch (band) {
        case 'excellent':
        case 'good':
            return 'border-success/40 bg-success/10 text-success';
        case 'fair':
            return 'border-chart-3/40 bg-chart-3/10 text-chart-3';
        case 'needs_work':
            return 'border-destructive/40 bg-destructive/10 text-destructive';
        default:
            return 'border-transparent bg-secondary text-secondary-foreground';
    }
}
