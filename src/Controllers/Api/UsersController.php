<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\UserAngelTypeReferenceResource;
use Engelsystem\Controllers\Api\Resources\UserDetailResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Controllers\Api\Resources\WorklogResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\Collection;

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

    public function entriesByAngeltype(Request $request): Response
    {
        $id = (int) $request->getAttribute('angeltype_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($id);

        /** @var User[]|Collection $models */
        $models = $angelType->userAngelTypes()
            ->orderBy('name')
            ->get();

        /** @var UserAngelType[]|Collection $models */
        $models = $models->map(function (User $model) {
            // Patch to use the existing user model instead of a partially populated one
            $model->pivot->setRelatedModel($model);
            return $model->pivot;
        });

        /** @var UserAngelTypeReferenceResource[]|Collection $models */
        $models = UserAngelTypeReferenceResource::collection($models);

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function worklogs(Request $request): Response
    {
        $id = (int) $request->getAttribute('user_id');
        /** @var User $user */
        $user = User::findOrFail($id);

        $models = $user->worklogs();

        $models = $models
            ->orderBy('worked_at')
            ->get();

        $models = WorklogResource::collection($models);

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }
}
