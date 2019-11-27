<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class Conference
{
    use CalculatesTime;

    /** @var string required */
    protected $title;

    /** @var string required */
    protected $acronym;

    /** @var string|null */
    protected $start;

    /** @var string|null */
    protected $end;

    /** @var int|null */
    protected $days;

    /** @var string|null */
    protected $timeslotDuration;

    /** @var string|null */
    protected $baseUrl;

    /**
     * Event constructor.
     *
     * @param string      $title
     * @param string      $acronym
     * @param string|null $start
     * @param string|null $end
     * @param int|null    $days
     * @param string|null $timeslotDuration
     * @param string|null $baseUrl
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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAcronym(): string
    {
        return $this->acronym;
    }

    /**
     * @return string|null
     */
    public function getStart(): ?string
    {
        return $this->start;
    }

    /**
     * @return string|null
     */
    public function getEnd(): ?string
    {
        return $this->end;
    }

    /**
     * @return int|null
     */
    public function getDays(): ?int
    {
        return $this->days;
    }

    /**
     * @return string|null
     */
    public function getTimeslotDuration(): ?string
    {
        return $this->timeslotDuration;
    }

    /**
     * @return int|null
     */
    public function getTimeslotDurationSeconds(): ?int
    {
        $duration = $this->getTimeslotDuration();
        if (!$duration) {
            return null;
        }

        return $this->secondsFromTime($duration);
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }
}
