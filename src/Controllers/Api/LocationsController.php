<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;

class LocationsController extends ApiController
{
    public function index(): Response
    {
        $models = Location::query()
            ->orderBy('name')
            ->get();

        $data = ['data' => LocationResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }
}
