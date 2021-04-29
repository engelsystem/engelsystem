<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\Extension\GlobalsInterface as GlobalsInterface;

class Globals extends TwigExtension implements GlobalsInterface
{
    /** @var Authenticator */
    protected $auth;

    /** @var Request */
    protected $request;

    /**
     * @param Authenticator $auth
     * @param Request       $request
     */
    public function __construct(Authenticator $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals(): array
    {
        $user = $this->auth->user();

        if ($user === null) {
            $themeId = config('theme');
        } else {
            $themeId = $user->settings->theme;
        }

        $theme = config('themes')[$themeId];

        return [
            'user'       => $user ?? [],
            'request'    => $this->request,
            'themeId'      => $themeId,
            'theme'      => $theme,
        ];
    }
}
