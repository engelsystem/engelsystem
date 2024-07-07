<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class ScheduleGenerator extends ScheduleData
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $version = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }
}
