<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Twig_Extension as TwigExtension;
use Twig_Extension_GlobalsInterface as GlobalsInterface;

class Globals extends TwigExtension implements GlobalsInterface
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
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        $user = $this->auth->user();

        return [
            'user' => $user ? $user : [],
        ];
    }
}
