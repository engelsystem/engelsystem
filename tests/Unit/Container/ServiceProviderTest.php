<?php

namespace Engelsystem\Test\Unit\Container;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Test\Unit\Container\Stub\ServiceProviderImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;

class ConfigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Container\ServiceProvider::__construct
     */
    public function testRegister()
    {
        $app = $this->getApp();

        $serviceProvider = new ServiceProviderImplementation($app);

        $this->assertInstanceOf(ServiceProvider::class, $serviceProvider);
    }
}
