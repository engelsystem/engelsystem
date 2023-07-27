<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use Engelsystem\Models\Room;

class RoomsController extends ApiController
{
    public function index(): Response
    {
        $models = Room::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $models->map(function (Room $model): void {
            $model->url = $this->url->to('/rooms', ['action' => 'view', 'room_id' => $model->id]);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }
}
