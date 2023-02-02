<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\Request;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Legacy extends TwigExtension
{
    public function __construct(protected Request $request)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        $isSafeHtml = ['is_safe' => ['html']];
        return [
            new TwigFunction('menu', 'make_navigation', $isSafeHtml),
            new TwigFunction('menuUserShiftState', 'User_shift_state_render', $isSafeHtml),
            new TwigFunction('menuUserHints', 'header_render_hints', $isSafeHtml),
            new TwigFunction('menuLanguages', 'make_language_select', $isSafeHtml),
            new TwigFunction('page', [$this, 'getPage']),
        ];
    }

    public function getPage(): string
    {
        if ($this->request->has('p')) {
            return $this->request->get('p');
        }

        return $this->request->path();
    }
}
