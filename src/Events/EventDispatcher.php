<?php

namespace Engelsystem\Events;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventDispatcher
{
    /** @var callable[] */
    protected $listeners;

    /**
     * @param array|string    $events
     * @param callable|string $listener
     */
    public function listen($events, $listener): void
    {
        foreach ((array)$events as $event) {
            $this->listeners[$event][] = $listener;
        }
    }

    /**
     * @param string $event
     */
    public function forget($event): void
    {
        unset($this->listeners[$event]);
    }

    /**
     * @param string|object $event
     * @param array|mixed   $payload
     * @param bool          $halt
     *
     * @return array|mixed|null
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }

    /**
     * @param string|object $event
     * @param array|mixed   $payload
     * @param bool          $halt     Stop on first non-null return
     *
     * @return array|null|mixed
     */
    public function dispatch($event, $payload = [], $halt = false)
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
