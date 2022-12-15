<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Schedule
{
    /** @var Day[] */
    protected array $day;

    /**
     * @param Day[]      $days
     */
    public function __construct(
        protected string $version,
        protected Conference $conference,
        array $days
    ) {
        $this->day = $days;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

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
