<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\Extension\GlobalsInterface as GlobalsInterface;

use function array_key_exists;

class Globals extends TwigExtension implements GlobalsInterface
{
    public function __construct(protected Authenticator $auth, protected Request $request)
    {
    }

    /**
     * Returns a list of global variables to add to the existing list.
     */
    public function getGlobals(): array
    {
        $user = $this->auth->user();
        $themes = config('themes');
        $userMessages = null;

        if ($user === null) {
            $themeId = config('theme');
        } else {
            $themeId = $user->settings->theme;
            $userMessages = $user
                ->messagesReceived()
                ->where('read', false)
                ->count();
        }

        $query = $this->request->query->get('theme');
        if (!is_null($query) && isset($themes[(int)$query])) {
            $themeId = (int)$query;
        }

        if (array_key_exists($themeId, $themes) === false) {
            $themeId = array_key_first($themes);
        }

        $theme = $themes[$themeId];

        return [
            'user'          => $user ?? [],
            'user_messages' => $userMessages,
            'request'       => $this->request,
            'themeId'       => $themeId,
            'theme'         => $theme,
        ];
    }
}
