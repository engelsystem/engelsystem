<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use ErrorException;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Minimum wrapper to map Laravel model events
 */
class LaravelEventDispatcher implements Dispatcher
{
    public function __construct(protected EventDispatcher $dispatcher)
    {
    }

    /**
     * @inheritdoc
     */
    public function listen($events, $listener = null): void
    {
        $this->dispatcher->listen($events, $listener);
    }

    /**
     * @inheritdoc
     */
    public function hasListeners($eventName): bool
    {
        throw new ErrorException('hasListeners is not implemented');
    }

    /**
     * @inheritdoc
     */
    public function subscribe($subscriber): void
    {
        throw new ErrorException('subscribe is not implemented');
    }

    /**
     * @inheritdoc
     */
    public function until($event, $payload = []): mixed
    {
        return $this->dispatch($event, $payload, true);
    }

    /**
     * @inheritdoc
     */
    public function dispatch($event, $payload = [], $halt = false): array|null
    {
        if (is_string($event)) {
            $name = $event;
            $event = new LaravelEvent($name, $payload, $halt);
        } elseif ($payload) {
            throw new ErrorException('$payload cant contain content when using event class');
        }

        $ret = $this->dispatcher->dispatch($event);

        return $halt ? null : [$ret];
    }

    /**
     * @inheritdoc
     */
    public function push($event, $payload = []): void
    {
        throw new ErrorException('push is not implemented');
    }

    /**
     * @inheritdoc
     */
    public function flush($event): void
    {
        throw new ErrorException('flush is not implemented');
    }

    /**
     * @inheritdoc
     */
    public function forget($event): void
    {
        $this->dispatcher->forget($event);
    }

    /**
     * @inheritdoc
     */
    public function forgetPushed(): void
    {
        throw new ErrorException('forgetPushed is not implemented');
    }
}
