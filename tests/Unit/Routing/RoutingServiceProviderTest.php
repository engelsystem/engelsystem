<?php

namespace Engelsystem\Test\Routing;

use Engelsystem\Application;
use Engelsystem\Routing\RoutingServiceProvider;
use Engelsystem\Routing\UrlGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class RoutingServiceProviderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Routing\RoutingServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UrlGenerator $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['make', 'instance'])
            ->getMock();

        $app->expects($this->once())
            ->method('make')
            ->with(UrlGenerator::class)
            ->willReturn($urlGenerator);

        $app->expects($this->once())
            ->method('instance')
            ->with('routing.urlGenerator', $urlGenerator);

        $serviceProvider = new RoutingServiceProvider($app);
        $serviceProvider->register();
    }
}
