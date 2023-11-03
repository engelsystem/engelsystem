<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

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
            ->get(['id', 'name', 'description']);

        $models->map(function (AngelType $model): void {
            $model->url = $this->getUrl($model);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function ofUser(Request $request): Response
    {
        $id = (int) $request->getAttribute('user_id');
        $model = User::findOrFail($id);

        $models = $model->userAngelTypes()->get([
            'angel_types.id',
            'angel_types.name',
            'angel_types.description',
            'angel_types.restricted',
        ]);
        $data = [];

        $models->map(function (AngelType $model) use (&$data): void {
            $model->confirmed = !$model->restricted || $model->pivot->supporter || $model->pivot->confirm_user_id;
            $model->supporter = $model->pivot->supporter;
            $model->url = $this->getUrl($model);
            $modelData = $model->attributesToArray();
            unset($modelData['restricted']);
            $data[] = $modelData;
        });

        $data = ['data' => $data];
        return $this->response
            ->withContent(json_encode($data));
    }

    protected function getUrl(AngelType $model): string
    {
        return $this->url->to('/angeltypes', ['action' => 'view', 'angeltype_id' => $model->id]);
    }
}
