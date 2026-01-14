<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Carbon\CarbonTimeZone;
use Engelsystem\Config\Config;
use Engelsystem\Environment;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Helpers\ConfigureEnvironmentServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ConfigureEnvironmentServiceProvider::class, 'register')]
#[CoversMethod(ConfigureEnvironmentServiceProvider::class, 'setupDevErrorHandler')]
class ConfigureEnvironmentServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $config = new Config(['timezone' => 'Australia/Eucla', 'environment' => 'production']);
        $this->app->instance('config', $config);

        $handler = new Handler();
        $this->app->instance('error.handler', $handler);

        $serviceProvider = $this->getMockBuilder(ConfigureEnvironmentServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['setTimeZone', 'displayErrors', 'errorReporting'])
            ->getMock();

        $serviceProvider->expects($this->exactly(2))
            ->method('setTimeZone')
            ->willReturnCallback(function (CarbonTimeZone $timeZone): void {
                $this->assertEquals('Australia/Eucla', $timeZone->getName());
            });
        $matcher = $this->exactly(3);
        $serviceProvider->expects($matcher)
            ->method('displayErrors')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(false, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(false, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(true, $parameters[0]);
                }
            });
        $serviceProvider->expects($this->exactly(1))
            ->method('errorReporting')
            ->with(E_ALL);

        $serviceProvider->register();
        $this->assertNotEquals(Environment::DEVELOPMENT, $handler->getEnvironment());

        $config->set('environment', 'development');
        $serviceProvider->register();
        $this->assertEquals(Environment::DEVELOPMENT, $handler->getEnvironment());
    }
}
