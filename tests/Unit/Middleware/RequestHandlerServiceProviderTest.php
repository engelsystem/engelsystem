<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\RequestHandler;
use Engelsystem\Middleware\RequestHandlerServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;

class RequestHandlerServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Middleware\RequestHandlerServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var RequestHandler|MockObject $requestHandler */
        $requestHandler = $this->createMock(RequestHandler::class);

        $app = $this->getApp(['make', 'instance', 'bind']);

        $app->expects($this->once())
            ->method('make')
            ->with(RequestHandler::class)
            ->willReturn($requestHandler);
        $app->expects($this->once())
            ->method('instance')
            ->with('request.handler', $requestHandler);
        $app->expects($this->once())
            ->method('bind')
            ->with(RequestHandler::class, 'request.handler');

        $serviceProvider = new RequestHandlerServiceProvider($app);
        $serviceProvider->register();
    }
}
