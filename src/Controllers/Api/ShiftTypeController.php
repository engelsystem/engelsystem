<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\ShiftTypeResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;

class ShiftTypeController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the "user_shifts" privilege: the website shows the name and description of a
     * shift type on every shift page
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return 'user_shifts';
    }

    public function index(): Response
    {
        $models = $this->withDetails(ShiftType::query())
            ->orderBy('name')
            ->get();

        $data = ['data' => $models->map(fn(ShiftType $shiftType) => $this->shiftTypeData($shiftType))];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('shifttype_id');
        /** @var ShiftType $shiftType */
        $shiftType = $this->withDetails(ShiftType::query())->findOrFail($id);

        $data = ['data' => $this->shiftTypeData($shiftType)];
        return $this->response
            ->withContent(json_encode($data));
    }

    protected function withDetails(BuilderContract $query): BuilderContract
    {
        if ($this->auth?->can('shifttypes.view')) {
            $query->with('neededAngelTypes.angelType');
        }

        return $query;
    }

    /**
     * The angel types needed by default are only shown on the website's shift type pages, which
     * require the "shifttypes.view" privilege
     */
    protected function shiftTypeData(ShiftType $shiftType): array
    {
        $neededAngelTypes = null;
        if ($this->auth?->can('shifttypes.view')) {
            $neededAngelTypes = $shiftType->neededAngelTypes
                ->filter(fn(NeededAngelType $neededAngelType) => $neededAngelType->count > 0)
                ->map(fn(NeededAngelType $neededAngelType) => [
                    'angel_type' => AngelTypeResource::toIdentifierArray($neededAngelType->angelType),
                    'count' => $neededAngelType->count,
                ])
                ->values();
        }

        return [
            ...(new ShiftTypeResource($shiftType))->toArray(),
            'needed_angel_types' => $neededAngelTypes,
        ];
    }
}
