<?php

namespace Demo\Plugin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;

class Controller extends BaseController
{
    protected array $permissions = [
        'user_settings',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Response $response
    ) {
    }

    public function handle(): Response
    {
        return $this->response->withView('demo');
    }

    public function save(Request $request): Response
    {
        $data = $this->validate($request, [
            'info' => 'optional',
        ]);

        $state = $this->auth->user()->state;
        $state->demo_info = $data['info'] ?: null;
        $state->save();

        return $this->handle();
    }
}
