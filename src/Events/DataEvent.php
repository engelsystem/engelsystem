<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Illuminate\Support\Collection;

/**
 * Used when basic data is used
 */
class DataEvent extends StoppableEvent
{
    use DataStore;

    public function __construct(protected string $name, array|Collection $data)
    {
        if (!$data instanceof Collection) {
            $data = Collection::make($data);
        }

        $this->data = $data;
    }
}
