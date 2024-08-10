<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Illuminate\Support\Collection;

class AngelTypeResource extends BasicResource
{
    protected Collection | BaseModel | AngelType $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'description' => $this->model->description,
            'restricted' => $this->model->restricted,
            'url' => url('/angeltypes', ['action' => 'view', 'angeltype_id' => $this->model->id]),
        ];
    }
}
