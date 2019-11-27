<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;

class RedirectServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('redirect', Redirector::class);
    }
}
