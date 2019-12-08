<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Schedule
{
    /** @var string */
    protected $version;

    /** @var Conference */
    protected $conference;

    /** @var Day[] */
    protected $day;

    /**
     * @param string     $version
     * @param Conference $conference
     * @param Day[]      $days
     */
    public function __construct(
        string $version,
        Conference $conference,
        array $days
    ) {
        $this->version = $version;
        $this->conference = $conference;
        $this->day = $days;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return Conference
     */
    public function getConference(): Conference
    {
        return $this->conference;
    }

    /**
     * @return Day[]
     */
    public function getDay(): array
    {
        return $this->day;
    }

    /**
     * @return Room[]
     */
    public function getRooms(): array
    {
        $rooms = [];
        foreach ($this->day as $day) {
            foreach ($day->getRoom() as $room) {
                $name = $room->getName();
                $rooms[$name] = $room;
            }
        }

        return $rooms;
    }


    /**
     * @return Carbon|null
     */
    public function getStartDateTime(): ?Carbon
    {
        $start = null;
        foreach ($this->day as $day) {
            $time = $day->getStart();
            if ($time > $start && $start) {
                continue;
            }

            $start = $time;
        }

        return $start;
    }

    /**
     * @return Carbon|null
     */
    public function getEndDateTime(): ?Carbon
    {
        $end = null;
        foreach ($this->day as $day) {
            $time = $day->getEnd();
            if ($time < $end && $end) {
                continue;
            }

            $end = $time;
        }

        return $end;
    }
}
