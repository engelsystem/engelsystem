<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use PhpExtended\HttpMessage\ResponseFactory;
use PhpExtended\HttpMessage\ServerRequestFactory;
use PhpExtended\HttpMessage\StreamFactory;
use PhpExtended\HttpMessage\UploadedFileFactory;
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
        foreach (
            [
                ServerRequestFactory::class => ['psr7.factory.request', ServerRequestFactoryInterface::class],
                ResponseFactory::class      => ['psr7.factory.response', ResponseFactoryInterface::class],
                UploadedFileFactory::class  => ['psr7.factory.upload', UploadedFileFactoryInterface::class],
                StreamFactory::class        => ['psr7.factory.stream', StreamFactoryInterface::class],
                PsrHttpFactory::class       => ['psr7.factory', HttpMessageFactoryInterface::class],
            ] as $class => $aliases
        ) {
            foreach ($aliases as $alias) {
                $this->app->bind($alias, $class);
            }
        }

        $this->app->bind('psr7.request', 'request');
        $this->app->bind(ServerRequestInterface::class, 'psr7.request');

        $this->app->bind('psr7.response', 'response');
        $this->app->bind(ResponseInterface::class, 'psr7.response');
    }
}
