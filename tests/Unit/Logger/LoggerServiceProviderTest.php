<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Logger\Logger;
use Engelsystem\Logger\LoggerServiceProvider;
use Engelsystem\Logger\UserAwareLogger;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LoggerInterface;

#[CoversMethod(LoggerServiceProvider::class, 'register')]
#[CoversMethod(LoggerServiceProvider::class, 'boot')]
class LoggerServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $serviceProvider = new LoggerServiceProvider($this->app);
        $serviceProvider->register();

        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get('logger'));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(LoggerInterface::class));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(Logger::class));
        $this->assertInstanceOf(UserAwareLogger::class, $this->app->get(UserAwareLogger::class));
    }

    public function testBoot(): void
    {
        $auth = $this->getStubBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getStub();
        $log = $this->getMockBuilder(UserAwareLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->app->instance(Authenticator::class, $auth);
        $this->app->instance(UserAwareLogger::class, $log);

        $log->expects($this->once())
            ->method('setAuth')
            ->with($auth);

        $serviceProvider = new LoggerServiceProvider($this->app);
        $serviceProvider->boot();
    }
}
