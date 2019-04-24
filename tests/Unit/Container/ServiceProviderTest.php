<?php

namespace Engelsystem\Test\Unit\Container;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Test\Unit\Container\Stub\ServiceProviderImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest as ServiceProviderTestCase;

class ServiceProviderTest extends ServiceProviderTestCase
{
    /**
     * @covers \Engelsystem\Container\ServiceProvider::__construct
     * @covers \Engelsystem\Container\ServiceProvider::register
     * @covers \Engelsystem\Container\ServiceProvider::boot
     */
    public function testRegister()
    {
        $app = $this->getApp();

        $serviceProvider = new ServiceProviderImplementation($app);

        $this->assertInstanceOf(ServiceProvider::class, $serviceProvider);

        $serviceProvider->register();
        $serviceProvider->boot();
    }
}
