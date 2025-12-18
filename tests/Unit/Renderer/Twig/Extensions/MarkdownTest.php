<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Markdown as MarkdownRenderer;
use Engelsystem\Renderer\Twig\Extensions\Markdown;

class MarkdownTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new Markdown(new MarkdownRenderer());
        $filters = $extension->getFilters();

        $this->assertFilterExists('markdown', [$extension, 'render'], $filters);
        $this->assertFilterExists('md', [$extension, 'render'], $filters);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::render
     */
    public function testRender(): void
    {
        $extension = new Markdown(new MarkdownRenderer());

        $this->assertEquals(
            '<p>&lt;i&gt;Lorem&lt;/i&gt; <em>&quot;Ipsum&quot;</em></p>',
            $extension->render('<i>Lorem</i> *"Ipsum"*'),
        );

        $this->assertEquals(
            '',
            $extension->render(null),
        );
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::render
     */
    public function testRenderHtml(): void
    {
        $extension = new Markdown(new MarkdownRenderer());

        $this->assertEquals(
            '<p><i>Lorem</i> <em>&quot;Ipsum&quot;</em></p>',
            $extension->render('<i>Lorem</i> *"Ipsum"*', false),
        );
    }
}
