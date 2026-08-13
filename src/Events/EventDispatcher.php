<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Illuminate\Support\Str;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var callable[] */
    protected array $listeners = [];
    /** @var callable[] */
    protected array $wildcards = [];

    public function listen(array|string $events, callable|string $listener): void
    {
        foreach ((array) $events as $event) {
            if (Str::contains($event, '*')) {
                $this->wildcards[$event][] = $listener;
            } else {
                $this->listeners[$event][] = $listener;
            }
        }
    }

    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    public function fire(string|object $event): mixed
    {
        return $this->dispatch($event);
    }

    public function dispatch(string|object $event, ?string $eventName = null): object
    {
        $name = $eventName ?? $event;
        if (is_object($event) && !$eventName) {
            $name = $event instanceof Event && $event->getName() ? $event->getName() : get_class($event);
        }
        $event = is_object($event) ? $event : new NullEvent($name);
        $isStoppable = $event instanceof StoppableEventInterface;

        $listeners = [];
        if (isset($this->listeners[$name])) {
            $listeners = $this->listeners[$name];
        }
        foreach ($this->wildcards as $key => $wildcardListeners) {
            if (!Str::is($key, $name)) {
                continue;
            }

            $listeners = array_merge($listeners, $wildcardListeners);
        }

        foreach ($listeners as $listener) {
            if ($isStoppable && $event->isPropagationStopped()) {
                return $event;
            }

            if (!is_callable($listener) && is_string($listener) && !Str::contains($listener, '@')) {
                $listener = $listener . '@handle';
            }

            app()->call($listener, ['event' => $event]);
        }

        return $event;
    }
}
