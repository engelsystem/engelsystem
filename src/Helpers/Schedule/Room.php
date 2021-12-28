<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class Room
{
    /**
     * @param Event[] $events
     */
    public function __construct(
        protected string $name,
        protected array $events = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Event[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param Event[] $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }
}
