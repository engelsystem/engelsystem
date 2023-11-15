<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Helpers\Carbon;

class DayOfEvent
{
    /**
     * @return The current day of the event.
     *         If "event_has_day0" is set to true in config,
     *         the first day of the event will be 0, else 1.
     *         Returns null if "event_start" is not set.
     */
    public static function get(Carbon $date = null): int | null
    {
        $startOfEvent = config('event_start');

        if (!$startOfEvent) {
            return null;
        }

        /** @var Carbon $startOfEvent */
        $startOfEvent = $startOfEvent->copy()->startOfDay();
        $date = $date ?: Carbon::now();

        $now = $date->startOfDay();
        $diff = $startOfEvent->diffInDays($now, false);

        if ($diff >= 0) {
            // The first day of the event (diff 0) should be 1.
            // The seconds day of the event (diff 1) should be 2.
            // Add one day to the diff.
            return $diff + 1;
        }

        if (config('event_has_day0') && $diff < 0) {
            // One day before the event (-1 diff) should day 0.
            // Two days before the event (-2 diff) should be -1.
            // Add one day to the diff.
            return $diff + 1;
        }


        // This is the remaining case where the diff is negative (before event).
        // One day before the event (-1 diff) should be day -1.
        // Two days before the event (-2 diff) should be day -2.
        // Return as it is.
        return $diff;
    }
}
