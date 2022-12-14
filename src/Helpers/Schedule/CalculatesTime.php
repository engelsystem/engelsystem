<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

trait CalculatesTime
{
    protected function secondsFromTime(string $time): int
    {
        $seconds = 0;
        $duration = explode(':', $time);

        foreach (array_slice($duration, 0, 2) as $key => $times) {
            $seconds += [60 * 60, 60][$key] * $times;
        }

        return $seconds;
    }
}
