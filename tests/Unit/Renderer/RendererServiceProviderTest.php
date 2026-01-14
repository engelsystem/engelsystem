<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\EngineInterface;
use Engelsystem\Renderer\HtmlEngine;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Renderer\RendererServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(RendererServiceProvider::class, 'register')]
#[CoversMethod(RendererServiceProvider::class, 'registerHtmlEngine')]
#[CoversMethod(RendererServiceProvider::class, 'registerRenderer')]
#[CoversMethod(RendererServiceProvider::class, 'boot')]
class RendererServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $renderer = $this->getStubBuilder(Renderer::class)
            ->getStub();
        $htmlEngine = $this->getStubBuilder(HtmlEngine::class)
            ->getStub();

        $app = $this->getAppMock(['make', 'instance', 'tag']);

        $app
            ->method('make')
            ->willReturnMap([
                [Renderer::class, [], $renderer],
                [HtmlEngine::class, [], $htmlEngine],
            ]);

        $matcher = $this->exactly(4);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use ($matcher, $renderer, $htmlEngine): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(Renderer::class, $parameters[0]);
                    $this->assertSame($renderer, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('renderer', $parameters[0]);
                    $this->assertSame($renderer, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(HtmlEngine::class, $parameters[0]);
                    $this->assertSame($htmlEngine, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('renderer.htmlEngine', $parameters[0]);
                    $this->assertSame($htmlEngine, $parameters[1]);
                }
            });

        $this->setExpects($app, 'tag', ['renderer.htmlEngine', ['renderer.engine']]);

        $serviceProvider = new RendererServiceProvider($app);
        $serviceProvider->register();
    }

    public function testBoot(): void
    {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->getMock();
        $engine1 = $this->getStubBuilder(EngineInterface::class)->getStub();
        $engine2 = $this->getStubBuilder(EngineInterface::class)->getStub();

        $app = $this->getAppMock(['get', 'tagged']);

        $this->setExpects($app, 'get', ['renderer'], $renderer);
        $this->setExpects($app, 'tagged', ['renderer.engine'], [$engine1, $engine2]);

        $matcher = $this->exactly(2);
        $renderer
            ->expects($matcher)
            ->method('addRenderer')
            ->willReturnCallback(function (...$parameters) use ($matcher, $engine1, $engine2): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($engine1, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($engine2, $parameters[0]);
                }
            });

        $serviceProvider = new RendererServiceProvider($app);
        $serviceProvider->boot();
    }
}
