<?php

declare(strict_types=1);

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $response = $this->app->make(Response::class);
        $this->app->instance(Response::class, $response);
        $this->app->instance(SymfonyResponse::class, $response);
        $this->app->instance('response', $response);
    }
}
