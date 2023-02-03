<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventDispatcher
{
    /** @var callable[] */
    protected array $listeners;

    public function listen(array|string $events, callable|string $listener): void
    {
        foreach ((array) $events as $event) {
            $this->listeners[$event][] = $listener;
        }
    }

    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    public function fire(string|object $event, mixed $payload = [], bool $halt = false): mixed
    {
        return $this->dispatch($event, $payload, $halt);
    }

    /**
     * @param bool          $halt     Stop on first non-null return
     */
    public function dispatch(string|object $event, mixed $payload = [], bool $halt = false): mixed
    {
        if (is_object($event)) {
            $payload = $event;
            $event = get_class($event);
        }

        $listeners = [];
        if (isset($this->listeners[$event])) {
            $listeners = $this->listeners[$event];
        }

        $responses = [];
        foreach ($listeners as $listener) {
            if (!is_callable($listener) && is_string($listener) && !Str::contains($listener, '@')) {
                $listener = $listener . '@handle';
            }

            $response = app()->call($listener, ['event' => $event] + Arr::wrap($payload));

            // Return the events response
            if ($halt && !is_null($response)) {
                return $response;
            }

            // Stop further event propagation
            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $halt ? null : $responses;
    }
}
