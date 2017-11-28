<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Exceptions\Handlers\Whoops;
use Whoops\Run as WhoopsRunner;

class ExceptionsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $errorHandler = $this->app->make(Handler::class);
        $this->addProductionHandler($errorHandler);
        $this->addDevelopmentHandler($errorHandler);
        $this->app->instance('error.handler', $errorHandler);
        $this->app->bind(Handler::class, 'error.handler');
        $errorHandler->register();
    }

    public function boot()
    {
        /** @var Handler $handler */
        $handler = $this->app->get('error.handler');
        $request = $this->app->get('request');

        $handler->setRequest($request);
    }

    /**
     * @param Handler $errorHandler
     */
    protected function addProductionHandler($errorHandler)
    {
        $handler = $this->app->make(Legacy::class);
        $this->app->instance('error.handler.production', $handler);
        $errorHandler->setHandler(Handler::ENV_PRODUCTION, $handler);
        $this->app->bind(HandlerInterface::class, 'error.handler.production');
    }

    /**
     * @param Handler $errorHandler
     */
    protected function addDevelopmentHandler($errorHandler)
    {
        $handler = $this->app->make(LegacyDevelopment::class);

        if (class_exists(WhoopsRunner::class)) {
            $handler = $this->app->make(Whoops::class);
        }

        $this->app->instance('error.handler.development', $handler);
        $errorHandler->setHandler(Handler::ENV_DEVELOPMENT, $handler);
    }
}
