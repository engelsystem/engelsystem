<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

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
    public function getFunctions()
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
    public function isAuthenticated()
    {
        return (bool)$this->auth->user();
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->isAuthenticated();
    }
}
