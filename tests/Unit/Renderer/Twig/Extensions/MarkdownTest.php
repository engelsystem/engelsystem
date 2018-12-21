<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Markdown;
use Parsedown;
use PHPUnit\Framework\MockObject\MockObject;

class MarkdownTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::getFilters
     */
    public function testGeFilters()
    {
        /** @var Parsedown|MockObject $renderer */
        $renderer = $this->createMock(Parsedown::class);

        $extension = new Markdown($renderer);
        $filters = $extension->getFilters();

        $this->assertExtensionExists('markdown', [$extension, 'render'], $filters);
        $this->assertExtensionExists('md', [$extension, 'render'], $filters);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::render
     */
    public function testRender()
    {
        /** @var Parsedown|MockObject $renderer */
        $renderer = $this->createMock(Parsedown::class);

        $return = '<p>Lorem <em>&quot;Ipsum&quot;</em></p>';
        $renderer->expects($this->once())
            ->method('text')
            ->with('Lorem *&quot;Ipsum&quot;*')
            ->willReturn($return);

        $extension = new Markdown($renderer);
        $this->assertEquals($return, $extension->render('Lorem *"Ipsum"*'));
    }
}
