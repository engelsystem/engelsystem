<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Illuminate\Contracts\Support\Arrayable;

class ShiftResource extends BasicResource
{
    public function toArray(array|Arrayable $location = []): array
    {
        return [
            'id' => $this->model->id,
            'title' => $this->model->title,
            'description' => $this->model->description,
            'starts_at' => $this->model->start,
            'ends_at' => $this->model->end,
            'location' => $location instanceof Arrayable ? $location->toArray() : $location,
            'shift_type' => (new ShiftTypeResource($this->model->shiftType))->toArray(),
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
            'url' => url('/shifts', ['action' => 'view', 'shift_id' => $this->model->id]),
        ];
    }
}
