<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class LocationResource extends BasicResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'url' => url('/locations', ['action' => 'view', 'location_id' => $this->model->id]),
        ];
    }
}
