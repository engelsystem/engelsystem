<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

class Session extends TwigExtension
{
    /** @var SymfonySession */
    protected $session;

    /**
     * @param SymfonySession $session
     */
    public function __construct(SymfonySession $session)
    {
        $this->session = $session;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('session_get', [$this->session, 'get']),
        ];
    }
}
