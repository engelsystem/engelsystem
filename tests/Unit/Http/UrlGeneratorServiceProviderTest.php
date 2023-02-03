<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\UrlGeneratorServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;

class UrlGeneratorServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\UrlGeneratorServiceProvider::register()
     */
    public function testRegister(): void
    {
        /** @var UrlGenerator|MockObject $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'bind']);

        $this->setExpects($app, 'make', [UrlGenerator::class], $urlGenerator);
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [UrlGenerator::class, $urlGenerator],
                ['http.urlGenerator', $urlGenerator],
                [UrlGeneratorInterface::class, $urlGenerator]
            );
        $app->expects($this->once())
            ->method('bind')
            ->with(UrlGeneratorInterface::class, UrlGenerator::class);

        $serviceProvider = new UrlGeneratorServiceProvider($app);
        $serviceProvider->register();
    }
}
