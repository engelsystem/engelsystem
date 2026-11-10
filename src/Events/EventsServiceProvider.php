<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Models\BaseModel;

class EventsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $dispatcher = $this->app->make(EventDispatcher::class);

        $this->app->instance(EventDispatcher::class, $dispatcher);
        $this->app->instance('events.dispatcher', $dispatcher);

        $laravelDispatcher = $this->app->make(LaravelEventDispatcher::class);
        BaseModel::setEventDispatcher($laravelDispatcher);

        $this->registerEvents($dispatcher);
    }

    protected function registerEvents(EventDispatcher $dispatcher): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        foreach ($config->get('event-handlers', []) as $event => $handlers) {
            foreach ((array) $handlers as $handler) {
                $dispatcher->listen($event, $handler);
            }
        }
    }
}
