<?php

namespace Engelsystem\Events;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;

class EventsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $dispatcher = $this->app->make(EventDispatcher::class);

        $this->app->instance(EventDispatcher::class, $dispatcher);
        $this->app->instance('events.dispatcher', $dispatcher);

        $this->registerEvents($dispatcher);
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    protected function registerEvents(EventDispatcher $dispatcher)
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        foreach ($config->get('event-handlers', []) as $event => $handlers) {
            foreach ((array)$handlers as $handler) {
                $dispatcher->listen($event, $handler);
            }
        }
    }
}
