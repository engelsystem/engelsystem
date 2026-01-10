<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\EngineInterface;
use Engelsystem\Renderer\Renderer;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversMethod(Renderer::class, 'render')]
#[CoversMethod(Renderer::class, 'addRenderer')]
class RendererTest extends TestCase
{
    public function testGet(): void
    {
        $renderer = new Renderer();

        $nullRenderer = $this->getMockBuilder(EngineInterface::class)->getMock();

        $nullRenderer->expects($this->atLeastOnce())
            ->method('canRender')
            ->willReturn(false);
        $renderer->addRenderer($nullRenderer);

        $mockRenderer = $this->getMockBuilder(EngineInterface::class)->getMock();

        $mockRenderer->expects($this->atLeastOnce())
            ->method('canRender')
            ->with('foo.template')
            ->willReturn(true);

        $mockRenderer->expects($this->atLeastOnce())
            ->method('get')
            ->with('foo.template', ['lorem' => 'ipsum'])
            ->willReturn('Rendered content');

        $renderer->addRenderer($mockRenderer);
        $data = $renderer->render('foo.template', ['lorem' => 'ipsum']);

        $this->assertEquals('Rendered content', $data);
    }

    public function testError(): void
    {
        $renderer = new Renderer();

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('critical');

        $renderer->setLogger($loggerMock);

        $data = $renderer->render('testing.template');
        $this->assertEquals('', $data);
    }
}
