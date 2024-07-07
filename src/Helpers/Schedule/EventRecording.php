<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class EventRecording extends ScheduleData
{
    public function __construct(
        protected string $license,
        protected bool $optOut,
        protected ?string $url = null,
        protected ?string $link = null
    ) {
    }

    public function getLicense(): string
    {
        return $this->license;
    }

    public function isOptOut(): bool
    {
        return $this->optOut;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }
}
