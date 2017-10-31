<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\EngineInterface;
use Engelsystem\Renderer\Renderer;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

class RendererTest extends TestCase
{
    public function testGet()
    {
        $renderer = new Renderer();

        /** @var MockObject|EngineInterface $nullRenderer */
        $nullRenderer = $this->getMockForAbstractClass(EngineInterface::class);

        $nullRenderer->expects($this->atLeastOnce())
            ->method('canRender')
            ->willReturn(false);
        $renderer->addRenderer($nullRenderer);

        /** @var MockObject|EngineInterface $mockRenderer */
        $mockRenderer = $this->getMockForAbstractClass(EngineInterface::class);

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

    public function testError()
    {
        $renderer = new Renderer();

        /** @var MockObject|LoggerInterface $loggerMock */
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error');

        $renderer->setLogger($loggerMock);

        $data = $renderer->render('testing.template');
        $this->assertEquals('', $data);
    }
}
