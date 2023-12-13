<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\UserDetailResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Response;

class UsersController extends ApiController
{
    public function __construct(Response $response, protected Authenticator $auth)
    {
        parent::__construct($response);
    }

    public function self(): Response
    {
        $user = $this->auth->user();

        $data = ['data' => (new UserDetailResource($user))->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }
}
