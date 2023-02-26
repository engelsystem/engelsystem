<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Session extends TwigExtension
{
    public function __construct(protected SymfonySession $session)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('session_get', [$this->session, 'get']),
            new TwigFunction('session_set', [$this->session, 'set']),
            new TwigFunction('session_pop', [$this, 'sessionPop']),
        ];
    }

    /**
     * Returns the requested attribute and removes it from the session
     */
    public function sessionPop(string $name, mixed $default = null): mixed
    {
        $value = $this->session->get($name, $default);
        $this->session->remove($name);

        return $value;
    }
}
