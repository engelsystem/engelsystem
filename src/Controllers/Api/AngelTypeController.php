<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypeController extends ApiController
{
    public function index(): Response
    {
        $models = AngelType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $models->map(function (AngelType $model): void {
            $model->url = $this->url->to('/angeltypes', ['action' => 'view', 'angeltype_id' => $model->id]);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }
}
