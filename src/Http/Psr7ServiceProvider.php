<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class Psr7ServiceProvider extends ServiceProvider
{
    public function register()
    {
        $psr17Factory = Psr17Factory::class;

        foreach (
            [
                'psr7.factory.request',
                ServerRequestFactoryInterface::class,
                'psr7.factory.response',
                ResponseFactoryInterface::class,
                'psr7.factory.upload',
                UploadedFileFactoryInterface::class,
                'psr7.factory.stream',
                StreamFactoryInterface::class,
            ] as $alias
        ) {
            $this->app->bind($alias, $psr17Factory);
        }

        $this->app->bind('psr7.factory', PsrHttpFactory::class);
        $this->app->bind(HttpMessageFactoryInterface::class, PsrHttpFactory::class);

        $this->app->bind('psr7.request', 'request');
        $this->app->bind(ServerRequestInterface::class, 'psr7.request');

        $this->app->bind('psr7.response', 'response');
        $this->app->bind(ResponseInterface::class, 'psr7.response');
    }
}
