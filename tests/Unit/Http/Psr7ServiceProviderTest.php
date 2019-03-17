<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\Psr7ServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class Psr7ServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\Psr7ServiceProvider::register()
     */
    public function testRegister()
    {
        $app = new Application;

        $serviceProvider = new Psr7ServiceProvider($app);
        $serviceProvider->register();

        foreach (
            [
                'psr7.factory.request',
                'psr7.factory.response',
                'psr7.factory.upload',
                'psr7.factory.stream',
                'psr7.factory',
                'psr7.request',
                'psr7.response',
                ServerRequestFactoryInterface::class,
                ResponseFactoryInterface::class,
                UploadedFileFactoryInterface::class,
                StreamFactoryInterface::class,
                HttpMessageFactoryInterface::class,
                ServerRequestInterface::class,
                ResponseInterface::class,
            ] as $id
        ) {
            $this->assertTrue(
                $app->has($id),
                sprintf('"%s" is not registered', $id)
            );
        }
    }
}
