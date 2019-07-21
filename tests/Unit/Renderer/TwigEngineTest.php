<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigEngine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig_Environment as Twig;
use Twig_LoaderInterface as LoaderInterface;

class TwigEngineTest extends TestCase
{
    /**
     * @covers \Engelsystem\Renderer\TwigEngine::__construct
     * @covers \Engelsystem\Renderer\TwigEngine::get
     */
    public function testGet()
    {
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);

        $path = 'foo.twig';
        $twig->expects($this->once())
            ->method('render')
            ->with($path, ['lorem' => 'ipsum', 'shared' => 'data'])
            ->willReturn('LoremIpsum data!');

        $engine = new TwigEngine($twig);
        $engine->share('shared', 'data');

        $return = $engine->get($path, ['lorem' => 'ipsum']);
        $this->assertEquals('LoremIpsum data!', $return);
    }


    /**
     * @covers \Engelsystem\Renderer\TwigEngine::canRender
     */
    public function testCanRender()
    {
        /** @var Twig|MockObject $twig */
        $twig = $this->createMock(Twig::class);
        /** @var LoaderInterface|MockObject $loader */
        $loader = $this->getMockForAbstractClass(LoaderInterface::class);

        $path = 'foo.twig';

        $twig->expects($this->once())
            ->method('getLoader')
            ->willReturn($loader);
        $loader->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(true);

        $engine = new TwigEngine($twig);
        $return = $engine->canRender($path);
        $this->assertTrue($return);
    }
}
