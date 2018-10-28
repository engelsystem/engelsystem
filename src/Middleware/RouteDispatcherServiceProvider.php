<?php

namespace Engelsystem\Middleware;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Server\MiddlewareInterface;

class RouteDispatcherServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        $options = [
            'cacheFile' => $this->app->get('path.cache.routes'),
        ];

        if ($config->get('environment') == 'development') {
            $options['cacheDisabled'] = true;
        }

        $this->app->alias(RouteDispatcher::class, 'route.dispatcher');

        $this->app
            ->when(RouteDispatcher::class)
            ->needs(FastRouteDispatcher::class)
            ->give(function () use ($options) {
                return $this->generateRouting($options);
            });

        $this->app
            ->when(RouteDispatcher::class)
            ->needs(MiddlewareInterface::class)
            ->give(LegacyMiddleware::class);
    }

    /**
     * Includes the routes.php file
     *
     * @param array $options
     * @return FastRouteDispatcher
     * @codeCoverageIgnore
     */
    protected function generateRouting(array $options = [])
    {
        $routesFile = config_path('routes.php');
        $routesCacheFile = $this->app->get('path.cache.routes');

        if (
            file_exists($routesCacheFile)
            && filemtime($routesFile) > filemtime($routesCacheFile)
        ) {
            unlink($routesCacheFile);
        }

        return \FastRoute\cachedDispatcher(function (RouteCollector $route) {
            require config_path('routes.php');
        }, $options);
    }
}
