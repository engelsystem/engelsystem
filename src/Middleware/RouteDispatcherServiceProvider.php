<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Plugins\Plugin;
use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Server\MiddlewareInterface;

use function FastRoute\cachedDispatcher as FRCashedDispatcher;

class RouteDispatcherServiceProvider extends ServiceProvider
{
    public function register(): void
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
     * @codeCoverageIgnore
     */
    protected function generateRouting(array $options = []): FastRouteDispatcher
    {
        $routesFile = config_path('routes.php');
        $routesCacheFile = $this->app->get('path.cache.routes');

        if (
            file_exists($routesCacheFile)
            && filemtime($routesFile) > filemtime($routesCacheFile)
        ) {
            unlink($routesCacheFile);
        }

        return FRCashedDispatcher(function (RouteCollector $route): void {
            require config_path('routes.php');

            /** @var Plugin $plugin */
            foreach ($this->app->tagged('plugin') as $plugin) {
                $plugin->loadRoutes($route);
            }
        }, $options);
    }
}
