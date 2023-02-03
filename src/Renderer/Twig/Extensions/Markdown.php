<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Parsedown;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;

class Markdown extends TwigExtension
{
    public function __construct(protected Parsedown $renderer)
    {
    }

    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];

        return [
            new TwigFilter('markdown', [$this, 'render'], $options),
            new TwigFilter('md', [$this, 'render'], $options),
        ];
    }

    public function render(string $text, bool $escapeHtml = true): string
    {
        return $this->renderer->setSafeMode($escapeHtml)->text($text);
    }
}
