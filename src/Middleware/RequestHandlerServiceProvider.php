<?php

namespace Engelsystem\Middleware;

use Engelsystem\Container\ServiceProvider;

class RequestHandlerServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var RequestHandler $requestHandler */
        $requestHandler = $this->app->make(RequestHandler::class);

        $this->app->instance('request.handler', $requestHandler);
        $this->app->bind(RequestHandler::class, 'request.handler');
    }
}
