<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Illuminate\Support\Collection;

class LocationResource extends BasicResource
{
    protected Collection | BaseModel | Location $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'description' => $this->model->description ?: '',
            'url' => url('/locations', ['action' => 'view', 'location_id' => $this->model->id]),
        ];
    }
}
