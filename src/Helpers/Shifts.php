<?php

declare(strict_types=1);

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

        /** @see User_get_shifts_sum_query to keep it in sync */
        return $config['enabled'] && (
                // Starts during night
                $start->hour >= $config['start'] && $start->hour < $config['end']
                // Ends during night
                || (
                    $end->hour > $config['start']
                    || $end->hour == $config['start'] && $end->minute > 0
                ) && $end->hour <= $config['end']
                // Starts before and ends after night
                || $start->hour <= $config['start'] && $end->hour >= $config['end']
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
