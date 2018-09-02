<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $request = $this->app->call([Request::class, 'createFromGlobals']);
        $this->app->instance(Request::class, $request);
        $this->app->instance(SymfonyRequest::class, $request);
        $this->app->instance('request', $request);
    }
}
