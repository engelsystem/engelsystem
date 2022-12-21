<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class Conference
{
    use CalculatesTime;

    /**
     * Event constructor.
     */
    public function __construct(
        protected string $title,
        protected string $acronym,
        protected ?string $start = null,
        protected ?string $end = null,
        protected ?int $days = null,
        protected ?string $timeslotDuration = null,
        protected ?string $baseUrl = null
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAcronym(): string
    {
        return $this->acronym;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function getTimeslotDuration(): ?string
    {
        return $this->timeslotDuration;
    }

    public function getTimeslotDurationSeconds(): ?int
    {
        $duration = $this->getTimeslotDuration();
        if (!$duration) {
            return null;
        }

        return $this->secondsFromTime($duration);
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }
}
