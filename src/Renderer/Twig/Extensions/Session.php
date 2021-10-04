<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

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
    public function getFunctions(): array
    {
        return [
            new TwigFunction('session_get', [$this->session, 'get']),
            new TwigFunction('session_set', [$this->session, 'set']),
        ];
    }
}
