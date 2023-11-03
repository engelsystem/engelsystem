<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use Engelsystem\Models\Location;

class LocationsController extends ApiController
{
    public function index(): Response
    {
        $models = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $models->map(function (Location $model): void {
            $model->url = $this->url->to('/locations', ['action' => 'view', 'location_id' => $model->id]);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }
}
