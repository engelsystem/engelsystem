<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Container;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Test\Unit\Container\Stub\ServiceProviderImplementation;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ServiceProvider::class, '__construct')]
#[CoversMethod(ServiceProvider::class, 'register')]
#[CoversMethod(ServiceProvider::class, 'boot')]
class ServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = $this->getAppStub();

        $serviceProvider = new ServiceProviderImplementation($app);

        $this->assertInstanceOf(ServiceProvider::class, $serviceProvider);

        $serviceProvider->register();
        $serviceProvider->boot();
    }
}
