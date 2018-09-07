<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;

class UrlGeneratorServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\UrlGeneratorServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UrlGenerator $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)
            ->getMock();

        $app = $this->getApp();

        $this->setExpects($app, 'make', [UrlGenerator::class], $urlGenerator);
        $this->setExpects($app, 'instance', ['http.urlGenerator', $urlGenerator]);

        $serviceProvider = new UrlGeneratorServiceProvider($app);
        $serviceProvider->register();
    }
}
