<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Authentication extends TwigExtension
{
    public function __construct(protected Authenticator $auth)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user', [$this, 'isAuthenticated']),
            new TwigFunction('is_guest', [$this, 'isGuest']),
            new TwigFunction('can', [$this->auth, 'can']),
            new TwigFunction('canAny', [$this->auth, 'canAny']),
        ];
    }

    public function isAuthenticated(): bool
    {
        return (bool) $this->auth->user();
    }

    public function isGuest(): bool
    {
        return !$this->isAuthenticated();
    }
}
