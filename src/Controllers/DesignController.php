<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

class DesignController extends BaseController
{
    /** @var Response */
    protected $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Show the design overview page
     *
     * @return Response
     */
    public function index()
    {
        $demoUser = (new User())->forceFill([
            'id' => 42,
            'name' => 'test',
        ]);
        $demoUser->__set('state', (new State())->forceFill([
            'user_id' => 42,
            'arrived' => true,
        ]));
        $demoUser2 = (new User())->forceFill([
            'id' => 1337,
            'name' => 'test2',
        ]);
        $demoUser2->__set('state', (new State())->forceFill([
            'user_id' => 1337,
            'arrived' => false,
        ]));

        return $this->response->withView(
            'pages/design',
            [
                'demo_user' => $demoUser,
                'demo_user_2' => $demoUser2,
            ]
        );
    }
}
