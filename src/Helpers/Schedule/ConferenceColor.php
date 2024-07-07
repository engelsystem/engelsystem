<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class ConferenceColor extends ScheduleData
{
    /**
     * @param array<string, string> $others type -> color
     */
    public function __construct(
        protected ?string $primary = null,
        protected ?string $background = null,
        protected array $others = []
    ) {
    }

    public function getPrimary(): ?string
    {
        return $this->primary;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function getOthers(): array
    {
        return $this->others;
    }
}
