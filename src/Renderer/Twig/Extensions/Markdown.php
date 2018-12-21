<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Parsedown;
use Twig_Extension as TwigExtension;
use Twig_Filter as TwigFilter;

class Markdown extends TwigExtension
{
    /** @var Parsedown */
    protected $renderer;

    /**
     * @param Parsedown $renderer
     */
    public function __construct(Parsedown $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];

        return [
            new TwigFilter('markdown', [$this, 'render'], $options),
            new TwigFilter('md', [$this, 'render'], $options),
        ];
    }

    /**
     * @param string $text
     * @return string
     */
    public function render($text): string
    {
        return $this->renderer->text(htmlspecialchars($text));
    }
}
