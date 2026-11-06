<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;

class LocationsController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the "locations.view" privilege, same as the location pages on the website
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return 'locations.view';
    }

    public function index(): Response
    {
        $models = $this->withDetails(Location::query())
            ->orderBy('name')
            ->get();

        $data = ['data' => $models->map(fn(Location $location) => $this->locationData($location))];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('location_id');
        /** @var Location $location */
        $location = $this->withDetails(Location::query())->findOrFail($id);

        $data = ['data' => $this->locationData($location)];
        return $this->response
            ->withContent(json_encode($data));
    }

    protected function withDetails(BuilderContract $query): BuilderContract
    {
        if ($this->auth?->can('admin_shifts')) {
            $query->with('neededAngelTypes.angelType');
        }

        if ($this->auth?->can('locations.edit')) {
            $query->withCount('shifts');
        }

        return $query;
    }

    /**
     * Needed angel types and the number of shifts are only shown on the website with the
     * "admin_shifts" and "locations.edit" privileges
     */
    protected function locationData(Location $location): array
    {
        $neededAngelTypes = null;
        if ($this->auth?->can('admin_shifts')) {
            $neededAngelTypes = $location->neededAngelTypes
                ->filter(fn(NeededAngelType $neededAngelType) => $neededAngelType->count > 0)
                ->map(fn(NeededAngelType $neededAngelType) => [
                    'angel_type' => AngelTypeResource::toIdentifierArray($neededAngelType->angelType),
                    'count' => $neededAngelType->count,
                ])
                ->values();
        }

        return [
            ...(new LocationResource($location))->toArray(),
            'needed_angel_types' => $neededAngelTypes,
            'shifts_count' => $this->auth?->can('locations.edit') ? $location->shifts_count : null,
        ];
    }
}
