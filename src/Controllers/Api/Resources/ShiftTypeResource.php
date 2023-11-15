<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class ShiftTypeResource extends BasicResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'description' => $this->model->description,
        ];
    }
}
