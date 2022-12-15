<?php

namespace Engelsystem\Helpers;

use Carbon\Carbon;

// Should be moved to the shift model if it's available
class Shifts
{
    /**
     * Check if a time range is a night shift
     */
    public static function isNightShift(Carbon $start, Carbon $end): bool
    {
        $config = config('night_shifts');

        return $config['enabled'] && (
                $start->hour >= $config['start'] && $start->hour < $config['end']
                || $end->hour >= $config['start'] && $end->hour < $config['end']
            );
    }

    /**
     * Calculate a shifts night multiplier
     */
    public static function getNightShiftMultiplier(Carbon $start, Carbon $end): float
    {
        if (!self::isNightShift($start, $end)) {
            return 1;
        }

        return config('night_shifts')['multiplier'];
    }
}
