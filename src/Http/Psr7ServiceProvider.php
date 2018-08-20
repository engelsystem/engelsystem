<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;


class Psr7ServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var DiactorosFactory $psr7Factory */
        $psr7Factory = $this->app->make(DiactorosFactory::class);
        $this->app->instance('psr7.factory', $psr7Factory);

        /** @var Request $request */
        $request = $this->app->get('request');
        $this->app->instance('psr7.request', $request);
        $this->app->bind(ServerRequestInterface::class, 'psr7.request');

        /** @var Response $response */
        $response = $this->app->get('response');
        $this->app->instance('psr7.response', $response);
        $this->app->bind(ResponseInterface::class, 'psr7.response');
    }
}
