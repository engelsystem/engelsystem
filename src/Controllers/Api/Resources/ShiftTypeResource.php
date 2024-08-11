<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Support\Collection;

class ShiftTypeResource extends BasicResource
{
    protected Collection | BaseModel | ShiftType $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'description' => $this->model->description,
        ];
    }
}
