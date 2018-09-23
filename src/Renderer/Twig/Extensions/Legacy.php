<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Http\Request;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

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
    public function getFunctions()
    {
        $isSafeHtml = ['is_safe' => ['html']];
        return [
            new TwigFunction('menu', 'make_navigation', $isSafeHtml),
            new TwigFunction('menuUserShiftState', 'User_shift_state_render', $isSafeHtml),
            new TwigFunction('menuUserMessages', 'user_unread_messages', $isSafeHtml),
            new TwigFunction('menuUserHints', 'header_render_hints', $isSafeHtml),
            new TwigFunction('menuUserSubmenu', 'make_user_submenu', $isSafeHtml),
            new TwigFunction('page', [$this, 'getPage']),
        ];
    }

    /**
     * @return string
     */
    public function getPage()
    {
        if ($this->request->has('p')) {
            return $this->request->get('p');
        }

        return $this->request->path();
    }
}
