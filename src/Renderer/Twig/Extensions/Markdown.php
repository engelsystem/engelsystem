<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Parsedown;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;

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
     * @param bool   $escapeHtml
     *
     * @return string
     */
    public function render(string $text, bool $escapeHtml = true): string
    {
        if ($escapeHtml) {
            $text = htmlspecialchars($text);
        }

        return $this->renderer->text($text);
    }
}
