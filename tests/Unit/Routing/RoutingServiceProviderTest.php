<?php

namespace Engelsystem\Test\Routing;

use Engelsystem\Routing\RoutingServiceProvider;
use Engelsystem\Routing\UrlGenerator;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;

class RoutingServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Routing\RoutingServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UrlGenerator $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)
            ->getMock();

        $app = $this->getApp();

        $this->setExpects($app, 'make', [UrlGenerator::class], $urlGenerator);
        $this->setExpects($app, 'instance', ['routing.urlGenerator', $urlGenerator]);

        $serviceProvider = new RoutingServiceProvider($app);
        $serviceProvider->register();
    }
}
