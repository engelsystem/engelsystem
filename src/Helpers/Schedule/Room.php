<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

class Room
{
    /** @var string required */
    protected $name;

    /** @var Event[] */
    protected $event;

    /**
     * Room constructor.
     *
     * @param string  $name
     * @param Event[] $events
     */
    public function __construct(
        string $name,
        array $events = []
    ) {
        $this->name = $name;
        $this->event = $events;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Event[]
     */
    public function getEvent(): array
    {
        return $this->event;
    }

    /**
     * @param Event[] $event
     */
    public function setEvent(array $event): void
    {
        $this->event = $event;
    }
}
