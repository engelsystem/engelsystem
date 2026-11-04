<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Illuminate\Support\Collection;

/**
 * Used as a basic data store
 */
trait DataStore
{
    public Collection $data;

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return $this->data->offsetExists($name);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }
}
