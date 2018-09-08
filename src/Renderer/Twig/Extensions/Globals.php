<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Twig_Extension as TwigExtension;
use Twig_Extension_GlobalsInterface as GlobalsInterface;

class Globals extends TwigExtension implements GlobalsInterface
{
    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        global $user;

        return [
            'user' => isset($user) ? $user : [],
        ];
    }
}
