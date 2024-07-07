<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Day extends ScheduleData
{
    /**
     * @param Room[] $rooms
     */
    public function __construct(
        protected string $date,
        protected Carbon $start,
        protected Carbon $end,
        protected int $index,
        protected array $rooms = []
    ) {
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getStart(): Carbon
    {
        return $this->start;
    }

    public function getEnd(): Carbon
    {
        return $this->end;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return Room[]
     */
    public function getRooms(): array
    {
        return $this->rooms;
    }
}
