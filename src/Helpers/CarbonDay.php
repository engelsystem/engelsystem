<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use DateTimeZone;

class CarbonDay extends Carbon
{
    public function __construct(
        float | DateTimeInterface | int | string | WeekDay | Month | null $time = null,
        int | DateTimeZone | string | null $timezone = null
    ) {
        parent::__construct($time, $timezone);
        $this->settings(['toStringFormat' => 'Y-m-d', 'toJsonFormat' => 'Y-m-d']);
        $this->startOfDay();
    }

    public static function createFromDay(string | int $day, DateTimeZone | string | int | null $timezone = null): ?self
    {
        return static::createFromFormat('Y-m-d', $day, $timezone)
            ->settings(['toStringFormat' => 'Y-m-d', 'toJsonFormat' => 'Y-m-d'])
            ->startOfDay();
    }
}
