<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Exceptions\Handler as ExceptionHandler;

class ExceptionsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $errorHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance('error.handler', $errorHandler);
    }
}
