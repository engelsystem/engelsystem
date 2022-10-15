<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\ChecksArrivalsAndDepartures;

class ChecksArrivalsAndDeparturesImplementation
{
    use ChecksArrivalsAndDepartures;

    public function checkArrival(?string $arrival, ?string $departure): bool
    {
        return $this->isArrivalDateValid($arrival, $departure);
    }

    public function checkDeparture(?string $arrival, ?string $departure): bool
    {
        return $this->isDepartureDateValid($arrival, $departure);
    }
}
