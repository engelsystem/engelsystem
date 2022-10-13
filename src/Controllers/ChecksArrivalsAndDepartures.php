<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use DateTime;

trait ChecksArrivalsAndDepartures
{
    protected function isArrivalDateValid(?string $arrival, ?string $departure): bool
    {
        if (is_null($arrival)) {
            return false; // since required value
        }

        $arrival_carbon = $this->toCarbon($arrival);

        if (!is_null($departure) && $arrival_carbon->greaterThan($this->toCarbon($departure))) {
            return false;
        }

        return !$this->isBeforeBuildup($arrival_carbon) && !$this->isAfterTeardown($arrival_carbon);
    }

    protected function isDepartureDateValid(?string $arrival, ?string $departure): bool
    {
        if (is_null($departure)) {
            return true; // since optional value
        }
        $departure_carbon = $this->toCarbon($departure);

        return $departure_carbon->greaterThanOrEqualTo($this->toCarbon($arrival)) &&
            !$this->isBeforeBuildup($departure_carbon) && !$this->isAfterTeardown($departure_carbon);
    }

    private function toCarbon(string $date_string): Carbon
    {
        return new Carbon(DateTime::createFromFormat('Y-m-d', $date_string));
    }

    private function isBeforeBuildup(Carbon $date): bool
    {
        $buildup = config('buildup_start');
        return !empty($buildup) && $date->lessThan($buildup->setTime(0,0));
    }

    private function isAfterTeardown(Carbon $date): bool
    {
        $teardown = config('teardown_end');
        return !empty($teardown) && $date->greaterThanOrEqualTo($teardown->addDay()->setTime(0,0));
    }
}
