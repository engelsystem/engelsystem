<?php

namespace Engelsystem\Routing;

use Engelsystem\Container\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $urlGenerator = $this->app->make(UrlGenerator::class);
        $this->app->instance('routing.urlGenerator', $urlGenerator);
    }
}
