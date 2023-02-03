<?php

declare(strict_types=1);

namespace Engelsystem\Exceptions;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Environment;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Exceptions\Handlers\Whoops;
use Psr\Log\LoggerInterface;
use Whoops\Run as WhoopsRunner;

class ExceptionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $errorHandler = $this->app->make(Handler::class);
        $this->addProductionHandler($errorHandler);
        $this->addDevelopmentHandler($errorHandler);
        $this->app->instance('error.handler', $errorHandler);
        $this->app->bind(Handler::class, 'error.handler');
        $errorHandler->register();
    }

    public function boot(): void
    {
        /** @var Handler $handler */
        $handler = $this->app->get('error.handler');
        $request = $this->app->get('request');

        $handler->setRequest($request);
        $this->addLogger($handler);
    }

    protected function addProductionHandler(Handler $errorHandler): void
    {
        $handler = $this->app->make(Legacy::class);
        $this->app->instance('error.handler.production', $handler);
        $errorHandler->setHandler(Environment::PRODUCTION, $handler);
        $this->app->bind(HandlerInterface::class, 'error.handler.production');
    }

    protected function addDevelopmentHandler(Handler $errorHandler): void
    {
        $handler = $this->app->make(LegacyDevelopment::class);

        if (class_exists(WhoopsRunner::class)) {
            $handler = $this->app->make(Whoops::class);
        }

        $this->app->instance('error.handler.development', $handler);
        $errorHandler->setHandler(Environment::DEVELOPMENT, $handler);
    }

    protected function addLogger(Handler $handler): void
    {
        foreach ($handler->getHandler() as $h) {
            if (!method_exists($h, 'setLogger')) {
                continue;
            }

            $log = $this->app->get(LoggerInterface::class);
            $h->setLogger($log);
        }
    }
}
