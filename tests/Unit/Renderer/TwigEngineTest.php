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
        $data = ['lorem' => 'ipsum'];

        $twig->expects($this->once())
            ->method('render')
            ->with($path, $data)
            ->willReturn('LoremIpsum!');

        $engine = new TwigEngine($twig);
        $return = $engine->get($path, $data);
        $this->assertEquals('LoremIpsum!', $return);
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
