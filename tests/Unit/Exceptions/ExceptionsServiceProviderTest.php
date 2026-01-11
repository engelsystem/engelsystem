<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Environment;
use Engelsystem\Exceptions\ExceptionsServiceProvider;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Exceptions\Handlers\Whoops;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LoggerInterface;

#[CoversMethod(ExceptionsServiceProvider::class, 'addDevelopmentHandler')]
#[CoversMethod(ExceptionsServiceProvider::class, 'addProductionHandler')]
#[CoversMethod(ExceptionsServiceProvider::class, 'register')]
#[CoversMethod(ExceptionsServiceProvider::class, 'boot')]
#[CoversMethod(ExceptionsServiceProvider::class, 'addLogger')]
class ExceptionsServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = $this->getAppMock(['make', 'instance', 'bind']);

        $handler = $this->createMock(Handler::class);
        $this->setExpects($handler, 'register');
        $legacyHandler = $this->createStub(Legacy::class);
        $developmentHandler = $this->createStub(LegacyDevelopment::class);

        $whoopsHandler = $this->getStubBuilder(Whoops::class)
            ->setConstructorArgs([$app])
            ->getStub();

        $matcher = $this->exactly(3);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use (
                $matcher,
                $legacyHandler,
                $whoopsHandler,
                $handler,
            ): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('error.handler.production', $parameters[0]);
                    $this->assertSame($legacyHandler, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('error.handler.development', $parameters[0]);
                    $this->assertSame($whoopsHandler, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('error.handler', $parameters[0]);
                    $this->assertSame($handler, $parameters[1]);
                }
            });

        $matcher = $this->exactly(4);
        $app->expects($matcher)
            ->method('make')
            ->willReturnCallback(function (...$parameters) use (
                $whoopsHandler,
                $developmentHandler,
                $legacyHandler,
                $handler,
                $matcher
            ) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(Handler::class, $parameters[0]);
                    return $handler;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Legacy::class, $parameters[0]);
                    return $legacyHandler;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(LegacyDevelopment::class, $parameters[0]);
                    return $developmentHandler;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(Whoops::class, $parameters[0]);
                    return $whoopsHandler;
                }
            });

        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('bind')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(HandlerInterface::class, $parameters[0]);
                    $this->assertSame('error.handler.production', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Handler::class, $parameters[0]);
                    $this->assertSame('error.handler', $parameters[1]);
                }
            });

        $matcher = $this->exactly(2);
        $handler->expects($matcher)
            ->method('setHandler')
            ->willReturnCallback(function (...$parameters) use ($matcher, $legacyHandler, $whoopsHandler): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(Environment::PRODUCTION, $parameters[0]);
                    $this->assertSame($legacyHandler, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Environment::DEVELOPMENT, $parameters[0]);
                    $this->assertSame($whoopsHandler, $parameters[1]);
                }
            });

        $serviceProvider = new ExceptionsServiceProvider($app);
        $serviceProvider->register();
    }

    public function testBoot(): void
    {
        $handlerImpl = $this->getStubBuilder(HandlerInterface::class)->getStub();

        $loggingHandler = $this->createMock(Legacy::class);

        $handler = $this->createMock(Handler::class);

        $request = $this->createStub(Request::class);

        $log = $this->getStubBuilder(LoggerInterface::class)->getStub();

        $handler->expects($this->exactly(2))
            ->method('setRequest')
            ->with($request);
        $handler->expects($this->exactly(2))
            ->method('getHandler')
            ->willReturnOnConsecutiveCalls([$handlerImpl], [$loggingHandler]);

        $loggingHandler->expects($this->once())
            ->method('setLogger')
            ->with($log);

        $app = $this->getAppMock(['get']);
        $matcher = $this->exactly(5);
        $app->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($log, $request, $handler, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('error.handler', $parameters[0]);
                    return $handler;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('request', $parameters[0]);
                    return $request;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('error.handler', $parameters[0]);
                    return $handler;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('request', $parameters[0]);
                    return $request;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(LoggerInterface::class, $parameters[0]);
                    return $log;
                }
            });

        $provider = new ExceptionsServiceProvider($app);
        $provider->boot();
        $provider->boot();
    }
}
