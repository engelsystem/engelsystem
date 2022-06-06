<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\Request;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Legacy extends TwigExtension
{
    /** @var Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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
            new TwigFunction('menuUserSubmenu', 'make_user_submenu', $isSafeHtml),
            new TwigFunction('page', [$this, 'getPage']),
            new TwigFunction('msg', 'msg', $isSafeHtml),
        ];
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        if ($this->request->has('p')) {
            return $this->request->get('p');
        }

        return $this->request->path();
    }
}
