<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Day
{
    /** @var string required */
    protected string $date;

    /** @var Carbon required */
    protected Carbon $start;

    /** @var Carbon required */
    protected Carbon $end;

    /** @var int required */
    protected int $index;

    /** @var Room[] */
    protected array $room;

    /**
     * Day constructor.
     *
     * @param Room[] $rooms
     */
    public function __construct(
        string $date,
        Carbon $start,
        Carbon $end,
        int $index,
        array $rooms = []
    ) {
        $this->date = $date;
        $this->start = $start;
        $this->end = $end;
        $this->index = $index;
        $this->room = $rooms;
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
    public function getRoom(): array
    {
        return $this->room;
    }
}
