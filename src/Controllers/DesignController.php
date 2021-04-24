<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

class DesignController extends BaseController
{
    /** @var Response */
    protected $response;

    /** @var Config */
    protected $config;

    /**
     * @param Response $response
     * @param Config   $config
     */
    public function __construct(Response $response, Config $config)
    {
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * Show the design overview page
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $demoUser = (new User())->forceFill([
            'id'   => 42,
            'name' => 'test',
        ]);
        $demoUser->__set('state', (new State())->forceFill([
            'user_id' => 42,
            'arrived' => true,
        ]));
        $demoUser2 = (new User())->forceFill([
            'id'   => 1337,
            'name' => 'test2',
        ]);
        $demoUser2->__set('state', (new State())->forceFill([
            'user_id' => 1337,
            'arrived' => false,
        ]));

        $themes = $this->config->get('available_themes');

        $data = [
            'demo_user'   => $demoUser,
            'demo_user_2' => $demoUser2,
            'themes'      => $themes,
        ];

        $theme = $request->get('theme');
        if (isset($themes[$theme])) {
            $data['theme'] = $theme;
        }

        return $this->response->withView(
            'pages/design',
            $data
        );
    }
}
