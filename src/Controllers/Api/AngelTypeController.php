<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;

class AngelTypeController extends ApiController
{
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
        $id = (int) $request->getAttribute('user_id');
        $model = User::findOrFail($id);
        $data = ['data' => UserAngelTypeResource::collection($model->userAngelTypes)];

        return $this->response
            ->withContent(json_encode($data));
    }
}
