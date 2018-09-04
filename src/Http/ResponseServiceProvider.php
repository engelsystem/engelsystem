<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $response = $this->app->make(Response::class);
        $this->app->instance('response', $response);
    }
}
