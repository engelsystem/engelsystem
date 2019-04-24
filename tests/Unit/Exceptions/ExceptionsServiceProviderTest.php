<?php

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Exceptions\ExceptionsServiceProvider;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Exceptions\Handlers\Whoops;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;

class ExceptionsServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::addDevelopmentHandler()
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::addProductionHandler()
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::register()
     */
    public function testRegister()
    {
        $app = $this->getApp(['make', 'instance', 'bind']);

        /** @var Handler|MockObject $handler */
        $handler = $this->createMock(Handler::class);
        $this->setExpects($handler, 'register');
        /** @var Legacy|MockObject $legacyHandler */
        $legacyHandler = $this->createMock(Legacy::class);
        /** @var LegacyDevelopment|MockObject $developmentHandler */
        $developmentHandler = $this->createMock(LegacyDevelopment::class);

        $whoopsHandler = $this->getMockBuilder(Whoops::class)
            ->setConstructorArgs([$app])
            ->getMock();

        $app->expects($this->exactly(3))
            ->method('instance')
            ->withConsecutive(
                ['error.handler.production', $legacyHandler],
                ['error.handler.development', $whoopsHandler],
                ['error.handler', $handler]
            );

        $app->expects($this->exactly(4))
            ->method('make')
            ->withConsecutive(
                [Handler::class],
                [Legacy::class],
                [LegacyDevelopment::class],
                [Whoops::class]
            )
            ->willReturnOnConsecutiveCalls(
                $handler,
                $legacyHandler,
                $developmentHandler,
                $whoopsHandler
            );

        $app->expects($this->exactly(2))
            ->method('bind')
            ->withConsecutive(
                [HandlerInterface::class, 'error.handler.production'],
                [Handler::class, 'error.handler']
            );

        $handler->expects($this->exactly(2))
            ->method('setHandler')
            ->withConsecutive(
                [Handler::ENV_PRODUCTION, $legacyHandler],
                [Handler::ENV_DEVELOPMENT, $whoopsHandler]
            );

        $serviceProvider = new ExceptionsServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::boot()
     */
    public function testBoot()
    {
        /** @var Handler|MockObject $handler */
        $handler = $this->createMock(Handler::class);

        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);

        $handler->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $app = $this->getApp(['get']);
        $app->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['error.handler'],
                ['request']
            )
            ->willReturnOnConsecutiveCalls(
                $handler,
                $request
            );

        $provider = new ExceptionsServiceProvider($app);
        $provider->boot();
    }
}
