<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigEngine;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Twig\Environment as Twig;
use Twig\Loader\LoaderInterface as LoaderInterface;

#[CoversMethod(TwigEngine::class, '__construct')]
#[CoversMethod(TwigEngine::class, 'get')]
#[CoversMethod(TwigEngine::class, 'canRender')]
class TwigEngineTest extends TestCase
{
    public function testGet(): void
    {
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


    public function testCanRender(): void
    {
        $twig = $this->createMock(Twig::class);
        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();

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
