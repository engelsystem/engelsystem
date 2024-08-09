<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\ShiftTypeResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\Shifts\ShiftType;

class ShiftTypeController extends ApiController
{
    use UsesAuth;

    public function index(): Response
    {
        $models = ShiftType::query()
            ->orderBy('name')
            ->get();

        $data = ['data' => ShiftTypeResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }
}
