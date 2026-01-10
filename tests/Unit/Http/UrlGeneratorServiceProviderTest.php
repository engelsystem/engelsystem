<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\UrlGeneratorServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UrlGeneratorServiceProvider::class, 'register')]
class UrlGeneratorServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $urlGenerator = $this->getStubBuilder(UrlGenerator::class)
            ->getStub();

        $app = $this->getAppMock(['make', 'instance', 'bind']);

        $this->setExpects($app, 'make', [UrlGenerator::class], $urlGenerator);
        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('instance')->willReturnCallback(function (...$parameters) use ($matcher, $urlGenerator): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(UrlGenerator::class, $parameters[0]);
                    $this->assertSame($urlGenerator, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('http.urlGenerator', $parameters[0]);
                    $this->assertSame($urlGenerator, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(UrlGeneratorInterface::class, $parameters[0]);
                    $this->assertSame($urlGenerator, $parameters[1]);
                }
            });
        $app->expects($this->once())
            ->method('bind')
            ->with(UrlGeneratorInterface::class, UrlGenerator::class);

        $serviceProvider = new UrlGeneratorServiceProvider($app);
        $serviceProvider->register();
    }
}
