<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\News;
use Illuminate\Support\Collection;

class NewsResource extends BasicResource
{
    protected Collection | BaseModel | News $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->title,
            'text' => $this->model->text,
            'is_meeting' => $this->model->is_meeting,
            'is_pinned' => $this->model->is_pinned,
            'is_highlighted' => $this->model->is_highlighted,
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
            'url' => url('/news/' . $this->model->id),
        ];
    }
}
