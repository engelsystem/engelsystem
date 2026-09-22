<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Psr\Http\Message\ServerRequestInterface;

class LocationsController extends ApiController
{
    use UsesAuth;

    /**
     * Locations don't require the "api" privilege - any authenticated user may read
     * them, same as /news.
     */
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return (bool) $this->auth?->user();
    }

    public function index(): Response
    {
        $models = Location::query()
            ->orderBy('name')
            ->get();

        $data = ['data' => LocationResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('location_id');
        /** @var Location $location */
        $location = Location::findOrFail($id);

        $data = ['data' => (new LocationResource($location))->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }
}
