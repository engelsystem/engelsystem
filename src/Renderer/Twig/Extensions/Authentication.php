<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Twig_Extension as TwigExtension;
use Twig_Function as TwigFunction;

class Authentication extends TwigExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_user', [$this, 'isAuthenticated']),
            new TwigFunction('is_guest', [$this, 'isGuest']),
            new TwigFunction('has_permission_to', [$this, 'checkAuth']),
        ];
    }

    public function isAuthenticated()
    {
        global $user;

        return !empty($user);
    }

    public function isGuest()
    {
        return !$this->isAuthenticated();
    }

    public function checkAuth($privilege)
    {
        global $privileges;

        return in_array($privilege, $privileges);
    }
}
