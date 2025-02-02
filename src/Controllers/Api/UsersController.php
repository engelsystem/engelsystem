<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\UserDetailResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\User;

class UsersController extends ApiController
{
    use UsesAuth;

    public function index(): Response
    {
        $models = User::query()
            ->orderBy('name')
            ->get();

        $models = $models->map(function (BaseModel $model) {
            return UserResource::toIdentifierArray($model);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function user(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $userData = $user->id == $this->auth->user()->id ? new UserDetailResource($user) : new UserResource($user);
        $data = ['data' => $userData->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }
}
