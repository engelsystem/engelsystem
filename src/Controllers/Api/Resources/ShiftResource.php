<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class ShiftResource extends BasicResource
{
    protected Collection | BaseModel | Shift $model;

    public function toArray(array | Arrayable $location = []): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->title,
            'description' => $this->model->description,
            'starts_at' => $this->model->start,
            'ends_at' => $this->model->end,
            'location' => LocationResource::toIdentifierArray($location),
            'shift_type' => ShiftTypeResource::toIdentifierArray($this->model->shiftType),
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
            'url' => url('/shifts', ['action' => 'view', 'shift_id' => $this->model->id]),
        ];
    }
}
