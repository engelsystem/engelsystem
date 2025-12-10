<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Markdown as MarkdownRenderer;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter;

class Markdown extends TwigExtension
{
    public function __construct(protected MarkdownRenderer $renderer)
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

    public function render(mixed $text, bool $escapeHtml = true): string
    {
        return $this->renderer
            ->render((string) $text, !$escapeHtml);
    }
}
