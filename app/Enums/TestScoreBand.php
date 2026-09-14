<?php

namespace App\Enums;

use App\Models\MonthlyTest;

enum TestScoreBand: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case NeedsWork = 'needs_work';

    /**
     * Human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Good => 'Good',
            self::Fair => 'Fair',
            self::NeedsWork => 'Needs work',
        };
    }

    /**
     * Derive the band for a given score against a month's test bands, falling
     * back to the standard 80/60/40 thresholds (matching MonthlyTestFactory's
     * defaults) when no MonthlyTest row exists yet for the month.
     */
    public static function forScore(int $score, ?MonthlyTest $monthlyTest): self
    {
        $excellentMin = $monthlyTest !== null ? $monthlyTest->band_excellent_min : 80;
        $goodMin = $monthlyTest !== null ? $monthlyTest->band_good_min : 60;
        $fairMin = $monthlyTest !== null ? $monthlyTest->band_fair_min : 40;

        return match (true) {
            $score >= $excellentMin => self::Excellent,
            $score >= $goodMin => self::Good,
            $score >= $fairMin => self::Fair,
            default => self::NeedsWork,
        };
    }
}
