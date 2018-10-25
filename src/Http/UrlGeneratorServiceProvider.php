<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;

class UrlGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $urlGenerator = $this->app->make(UrlGenerator::class);
        $this->app->instance(UrlGenerator::class, $urlGenerator);
        $this->app->instance('http.urlGenerator', $urlGenerator);
        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);
    }
}
