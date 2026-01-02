<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\CarbonInterval;

class Carbon extends \Carbon\Carbon
{
    public const DATETIME_LOCAL = '!Y-m-d\TH:i';

    public const DATETIME_FALLBACK = '!Y-m-d H:i';

    public const DATETIME_FORMATS = [
        self::DATETIME_LOCAL,
        self::DATETIME_FALLBACK,
        self::DEFAULT_TO_STRING_FORMAT,
    ];

    /**
     * Parses HTML datetime-local and ISO date/time strings.
     *
     * @return self|null Carbon if parseable, else null
     * @see self::DATETIME_FORMATS
     */
    public static function createFromDatetime(string $value): ?\Carbon\Carbon
    {
        foreach (self::DATETIME_FORMATS as $datetimeFormat) {
            if (self::canBeCreatedFromFormat($value, $datetimeFormat)) {
                return self::createFromFormat($datetimeFormat, $value);
            }
        }

        return null;
    }

    /**
     * Formats a CarbonInterval into a human-readable duration string consisting of hours and minutes.
     * Format is defined in the localization files under 'general.duration.format'.
     *
     * @param CarbonInterval $interval The interval to format
     * @param string $format The format string, e.g. '%dh %02dm'
     * @return string The formatted duration string
     */
    public static function formatDuration(CarbonInterval $interval, string $format): string
    {
        $interval->cascade();
        $hours = floor($interval->totalHours);
        $minutes = $interval->minutes;

        return sprintf($format, $hours, $minutes);
    }
}
