<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $request = $this->app->call([Request::class, 'createFromGlobals']);
        $this->app->instance('request', $request);
    }
}
