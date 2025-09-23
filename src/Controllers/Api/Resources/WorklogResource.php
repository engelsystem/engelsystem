<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class WorklogResource extends BasicResource
{
    protected Collection | BaseModel | Pivot | Worklog $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'description' => $this->model->description,
            'hours' => $this->model->hours,
            'created_by' => UserResource::toIdentifierArray($this->model->creator),
            'worked_at' => $this->model->worked_at,
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
        ];
    }
}
