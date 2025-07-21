<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Schedule extends ScheduleData
{
    /**
     * @param Day[] $days
     */
    public function __construct(
        protected string $version,
        protected Conference $conference,
        protected array $days,
        protected ?ScheduleGenerator $generator = null,
    ) {
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
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @return Room[]
     *
     * Returns a list of all rooms for all days
     */
    public function getAllRooms(): array
    {
        $rooms = [];
        foreach ($this->days as $day) {
            foreach ($day->getRooms() as $room) {
                $rooms[] = $room;
            }
        }

        return $rooms;
    }

    /**
     * @return Room[]
     *
     * Returns a list of rooms unique by name and thus the instance of the last day they are used
     */
    public function getRooms(): array
    {
        $rooms = [];
        foreach ($this->getAllRooms() as $room) {
            $name = $room->getName();
            $rooms[$name] = $room;
        }

        return $rooms;
    }

    public function getStartDateTime(): ?Carbon
    {
        $start = null;
        foreach ($this->days as $day) {
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
        foreach ($this->days as $day) {
            $time = $day->getEnd();
            if ($time < $end && $end) {
                continue;
            }

            $end = $time;
        }

        return $end;
    }

    public function getGenerator(): ?ScheduleGenerator
    {
        return $this->generator;
    }
}
