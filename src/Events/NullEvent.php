<?php

declare(strict_types=1);

namespace Engelsystem\Events;

/**
 * Empty event, used when no data is needed
 */
class NullEvent extends StoppableEvent
{
    public function __construct(protected ?string $name = null)
    {
    }
}
