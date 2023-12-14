<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypeController extends ApiController
{
    use UsesAuth;

    public function index(): Response
    {
        $models = AngelType::query()
            ->orderBy('name')
            ->get();

        $data = ['data' => AngelTypeResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function ofUser(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $data = ['data' => UserAngelTypeResource::collection($user->userAngelTypes)];

        return $this->response
            ->withContent(json_encode($data));
    }
}
