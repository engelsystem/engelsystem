<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

abstract class ScheduleData
{
    public function patch(string $key, mixed $value): void
    {
        $this->$key = $value;
    }
}
