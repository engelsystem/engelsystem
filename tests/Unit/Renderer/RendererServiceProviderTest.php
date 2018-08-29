<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\EngineInterface;
use Engelsystem\Renderer\HtmlEngine;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Renderer\RendererServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;

class RendererServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Renderer\RendererServiceProvider::register()
     * @covers \Engelsystem\Renderer\RendererServiceProvider::registerRenderer()
     * @covers \Engelsystem\Renderer\RendererServiceProvider::registerHtmlEngine()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Renderer $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->getMock();
        /** @var PHPUnit_Framework_MockObject_MockObject|HtmlEngine $htmlEngine */
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
    public function testBoot()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Renderer $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->getMock();
        /** @var PHPUnit_Framework_MockObject_MockObject|EngineInterface $engine1 */
        $engine1 = $this->getMockForAbstractClass(EngineInterface::class);
        /** @var PHPUnit_Framework_MockObject_MockObject|EngineInterface $engine2 */
        $engine2 = $this->getMockForAbstractClass(EngineInterface::class);

        $app = $this->getApp(['get', 'tagged']);

        $engines = [$engine1, $engine2];

        $this->setExpects($app, 'get', ['renderer'], $renderer);
        $this->setExpects($app, 'tagged', ['renderer.engine'], $engines);

        $invocation = $renderer
            ->expects($this->exactly(count($engines)))
            ->method('addRenderer');
        call_user_func_array([$invocation, 'withConsecutive'], $engines);

        $serviceProvider = new RendererServiceProvider($app);
        $serviceProvider->boot();
    }
}
