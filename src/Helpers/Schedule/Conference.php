<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class Conference
{
    use CalculatesTime;

    /** @var string required */
    protected string $title;

    /** @var string required */
    protected string $acronym;

    protected ?string $start = null;

    protected ?string $end = null;

    protected ?int $days = null;

    protected ?string $timeslotDuration = null;

    protected ?string $baseUrl = null;

    /**
     * Event constructor.
     *
     */
    public function __construct(
        string $title,
        string $acronym,
        ?string $start = null,
        ?string $end = null,
        ?int $days = null,
        ?string $timeslotDuration = null,
        ?string $baseUrl = null
    ) {
        $this->title = $title;
        $this->acronym = $acronym;
        $this->start = $start;
        $this->end = $end;
        $this->days = $days;
        $this->timeslotDuration = $timeslotDuration;
        $this->baseUrl = $baseUrl;
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
