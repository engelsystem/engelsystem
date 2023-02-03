<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Container\ServiceProvider;

class SessionHandlerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app
            ->when(SessionHandler::class)
            ->needs('$paths')
            ->give(function () {
                return [
                    '/api',
                    '/atom',
                    '/rss',
                    '/health',
                    '/ical',
                    '/metrics',
                    '/shifts-json-export',
                    '/stats',
                ];
            });
    }
}
