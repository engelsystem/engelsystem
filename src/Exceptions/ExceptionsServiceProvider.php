<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Container\ServiceProvider;

class ExceptionsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $errorHandler = $this->app->make(BasicHandler::class);
        $errorHandler->register();
        $this->app->instance('error.handler', $errorHandler);
        $this->app->bind(Handler::class, 'error.handler');
    }
}
