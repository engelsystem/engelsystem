<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Psr\SimpleCache\InvalidArgumentException;

class Carbon extends \Carbon\Carbon
{
    public const DATETIME_LOCAL = '!Y-m-d\TH:i';

    public const DATETIME_FALLBACK = '!Y-m-d H:i';

    public const DATETIME_FORMATS = [
        self::DATETIME_LOCAL,
        self::DATETIME_FALLBACK,
    ];

    /**
     * Parses HTML datetime-local and ISO date/time strings.
     *
     * @param string $value
     * @return \Carbon\Carbon|null Carbon if parseable, else null
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
     * Parses HTML datetime-local and ISO date/time strings.
     *
     * @param string $value
     * @return int|null Timestamp if parseable, else null
     * @see self::DATETIME_FORMATS
     */
    public static function createTimestampFromDatetime(string $value): ?int
    {
        $carbon = self::createFromDateTime($value);
        return $carbon === null ? null : $carbon->timestamp;
    }
}
