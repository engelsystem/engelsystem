<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\RequestHandler;
use Engelsystem\Middleware\RequestHandlerServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(RequestHandlerServiceProvider::class, 'register')]
class RequestHandlerServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $requestHandler = $this->createStub(RequestHandler::class);

        $app = $this->getAppMock(['make', 'instance', 'bind']);

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
