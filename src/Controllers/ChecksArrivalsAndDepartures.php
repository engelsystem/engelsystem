<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use DateTime;

trait ChecksArrivalsAndDepartures
{
    protected function isArrivalDateValid(?string $arrival, ?string $departure): bool
    {
        $arrival_carbon = $this->toCarbon($arrival);
        $departure_carbon = $this->toCarbon($departure);

        if (is_null($arrival_carbon)) {
            return false; // since required value
        }

        if (!is_null($departure_carbon) && $arrival_carbon->greaterThan($departure_carbon)) {
            return false;
        }

        return !$this->isBeforeBuildup($arrival_carbon) && !$this->isAfterTeardown($arrival_carbon);
    }

    protected function isDepartureDateValid(?string $arrival, ?string $departure): bool
    {
        $arrival_carbon = $this->toCarbon($arrival);
        $departure_carbon = $this->toCarbon($departure);

        if (is_null($departure_carbon)) {
            return true; // since optional value
        }

        if (is_null($arrival_carbon)) {
            return false; // Will be false any ways
        }

        return $departure_carbon->greaterThanOrEqualTo($arrival_carbon) &&
            !$this->isBeforeBuildup($departure_carbon) && !$this->isAfterTeardown($departure_carbon);
    }

    private function toCarbon(?string $date_string): ?Carbon
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date_string ?: '');
        return $dateTime ? new Carbon($dateTime) : null;
    }

    private function isBeforeBuildup(Carbon $date): bool
    {
        $buildup = config('buildup_start');
        return !empty($buildup) && $date->lessThan($buildup->startOfDay());
    }

    private function isAfterTeardown(Carbon $date): bool
    {
        $teardown = config('teardown_end');
        return !empty($teardown) && $date->greaterThanOrEqualTo($teardown->endOfDay());
    }
}
