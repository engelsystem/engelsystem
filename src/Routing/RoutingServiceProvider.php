<?php
namespace Engelsystem\Routing;

use Engelsystem\Container\ServiceProvider;

/**
 * Registers the url generator depending on config.
 */
class RoutingServiceProvider extends ServiceProvider
{

    public function register()
    {
        $config = $this->app->get('config');
        $class = UrlGenerator::class;
        if (! $config->get('rewrite_urls', true)) {
            $class = LegacyUrlGenerator::class;
        }

        $urlGenerator = $this->app->make($class);
        $this->app->instance('routing.urlGenerator', $urlGenerator);
        $this->app->bind(UrlGeneratorInterface::class, 'routing.urlGenerator');
    }
}
