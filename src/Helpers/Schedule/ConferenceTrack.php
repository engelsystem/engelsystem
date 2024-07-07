<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class ConferenceTrack extends ScheduleData
{
    public function __construct(
        protected string $name,
        protected ?string $color = null,
        protected ?string $slug = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
