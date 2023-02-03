<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Markdown;
use Parsedown;

class MarkdownTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::getFilters
     */
    public function testGeFilters(): void
    {
        $extension = new Markdown(new Parsedown());
        $filters = $extension->getFilters();

        $this->assertExtensionExists('markdown', [$extension, 'render'], $filters);
        $this->assertExtensionExists('md', [$extension, 'render'], $filters);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::render
     */
    public function testRender(): void
    {
        $extension = new Markdown(new Parsedown());

        $this->assertEquals(
            '<p>&lt;i&gt;Lorem&lt;/i&gt; <em>&quot;Ipsum&quot;</em></p>',
            $extension->render('<i>Lorem</i> *"Ipsum"*'),
        );
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Markdown::render
     */
    public function testRenderHtml(): void
    {
        $renderer = new Parsedown();
        $extension = new Markdown($renderer);

        $this->assertEquals(
            '<p><i>Lorem</i> <em>&quot;Ipsum&quot;</em></p>',
            $extension->render('<i>Lorem</i> *"Ipsum"*', false),
        );
    }
}
