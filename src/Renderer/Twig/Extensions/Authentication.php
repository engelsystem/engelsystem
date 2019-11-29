<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Authentication extends TwigExtension
{
    /** @var Authenticator */
    protected $auth;

    /**
     * @param Authenticator $auth
     */
    public function __construct(Authenticator $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user', [$this, 'isAuthenticated']),
            new TwigFunction('is_guest', [$this, 'isGuest']),
            new TwigFunction('has_permission_to', [$this->auth, 'can']),
        ];
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return (bool)$this->auth->user();
    }

    /**
     * @return bool
     */
    public function isGuest(): bool
    {
        return !$this->isAuthenticated();
    }
}
