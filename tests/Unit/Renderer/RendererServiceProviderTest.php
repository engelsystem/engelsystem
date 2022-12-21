<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\EngineInterface;
use Engelsystem\Renderer\HtmlEngine;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Renderer\RendererServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;

class RendererServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Renderer\RendererServiceProvider::register()
     * @covers \Engelsystem\Renderer\RendererServiceProvider::registerHtmlEngine()
     * @covers \Engelsystem\Renderer\RendererServiceProvider::registerRenderer()
     */
    public function testRegister(): void
    {
        /** @var Renderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->getMock();
        /** @var HtmlEngine|MockObject $htmlEngine */
        $htmlEngine = $this->getMockBuilder(HtmlEngine::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'tag']);

        $app->expects($this->exactly(2))
            ->method('make')
            ->withConsecutive(
                [Renderer::class],
                [HtmlEngine::class]
            )->willReturnOnConsecutiveCalls(
                $renderer,
                $htmlEngine
            );

        $app->expects($this->exactly(4))
            ->method('instance')
            ->withConsecutive(
                [Renderer::class, $renderer],
                ['renderer', $renderer],
                [HtmlEngine::class, $htmlEngine],
                ['renderer.htmlEngine', $htmlEngine]
            );

        $this->setExpects($app, 'tag', ['renderer.htmlEngine', ['renderer.engine']]);

        $serviceProvider = new RendererServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Renderer\RendererServiceProvider::boot()
     */
    public function testBoot(): void
    {
        /** @var Renderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->getMock();
        /** @var EngineInterface|MockObject $engine1 */
        $engine1 = $this->getMockForAbstractClass(EngineInterface::class);
        /** @var EngineInterface|MockObject $engine2 */
        $engine2 = $this->getMockForAbstractClass(EngineInterface::class);

        $app = $this->getApp(['get', 'tagged']);

        $this->setExpects($app, 'get', ['renderer'], $renderer);
        $this->setExpects($app, 'tagged', ['renderer.engine'], [$engine1, $engine2]);

        $renderer
            ->expects($this->exactly(2))
            ->method('addRenderer')
            ->withConsecutive([$engine1], [$engine2]);

        $serviceProvider = new RendererServiceProvider($app);
        $serviceProvider->boot();
    }
}
