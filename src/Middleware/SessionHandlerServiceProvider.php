<?php

namespace Engelsystem\Middleware;

use Engelsystem\Container\ServiceProvider;

class SessionHandlerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app
            ->when(SessionHandler::class)
            ->needs('$paths')
            ->give(function () {
                return [
                    '/api',
                    '/ical',
                    '/metrics',
                    '/shifts-json-export',
                    '/stats',
                ];
            });
    }
}
