<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Logger\Logger;
use Engelsystem\Logger\LoggerServiceProvider;
use Engelsystem\Logger\UrlAwareLogger;
use Engelsystem\Logger\UserAwareLogger;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class LoggerServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Logger\LoggerServiceProvider::register
     */
    public function testRegister(): void
    {
        $serviceProvider = new LoggerServiceProvider($this->app);
        $serviceProvider->register();

        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get('logger'));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(LoggerInterface::class));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(Logger::class));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(UrlAwareLogger::class));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(UserAwareLogger::class));
    }

    /**
     * @covers \Engelsystem\Logger\LoggerServiceProvider::boot
     */
    public function testBoot(): void
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var UserAwareLogger|MockObject $log */
        $log = $this->getMockBuilder(UserAwareLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->app->instance(Authenticator::class, $auth);
        $this->app->instance(Request::class, $request);
        $this->app->instance(UserAwareLogger::class, $log);

        $log->expects($this->once())
            ->method('setAuth')
            ->with($auth);

        $log->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $serviceProvider = new LoggerServiceProvider($this->app);
        $serviceProvider->boot();
    }
}
