<?php

namespace Engelsystem\Test\Unit\Routing;

use Engelsystem\Config\Config;
use Engelsystem\Routing\LegacyUrlGenerator;
use Engelsystem\Routing\RoutingServiceProvider;
use Engelsystem\Routing\UrlGenerator;
use Engelsystem\Routing\UrlGeneratorInterface;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RoutingServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Routing\RoutingServiceProvider::register()
     */
    public function testRegister()
    {
        $app = $this->getApp(['make', 'instance', 'bind', 'get']);
        /** @var MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)->getMock();
        /** @var MockObject|UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->getMockForAbstractClass(UrlGeneratorInterface::class);
        /** @var MockObject|UrlGeneratorInterface $legacyUrlGenerator */
        $legacyUrlGenerator = $this->getMockForAbstractClass(UrlGeneratorInterface::class);

        $config->expects($this->atLeastOnce())
            ->method('get')
            ->with('rewrite_urls')
            ->willReturnOnConsecutiveCalls(
                true,
                false
            );

        $this->setExpects($app, 'get', ['config'], $config, $this->atLeastOnce());

        $app->expects($this->atLeastOnce())
            ->method('make')
            ->withConsecutive(
                [UrlGenerator::class],
                [LegacyUrlGenerator::class]
            )
            ->willReturnOnConsecutiveCalls(
                $urlGenerator,
                $legacyUrlGenerator
            );
        $app->expects($this->atLeastOnce())
            ->method('instance')
            ->withConsecutive(
                ['routing.urlGenerator', $urlGenerator],
                ['routing.urlGenerator', $legacyUrlGenerator]
            );
        $this->setExpects(
            $app, 'bind',
            [UrlGeneratorInterface::class, 'routing.urlGenerator'], null,
            $this->atLeastOnce()
        );

        $serviceProvider = new RoutingServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->register();
    }
}
