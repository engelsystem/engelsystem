<?php

namespace Engelsystem\Middleware;

use Engelsystem\Container\ServiceProvider;
use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Server\MiddlewareInterface;

class RouteDispatcherServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias(RouteDispatcher::class, 'route.dispatcher');

        $this->app
            ->when(RouteDispatcher::class)
            ->needs(FastRouteDispatcher::class)
            ->give(function () {
                return $this->generateRouting();
            });

        $this->app
            ->when(RouteDispatcher::class)
            ->needs(MiddlewareInterface::class)
            ->give(LegacyMiddleware::class);
    }

    /**
     * Includes the routes.php file
     *
     * @return FastRouteDispatcher
     * @codeCoverageIgnore
     */
    function generateRouting()
    {
        return \FastRoute\simpleDispatcher(function (RouteCollector $route) {
            require config_path('routes.php');
        });
    }
}
