<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class NewsResource extends BasicResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'title' => $this->model->title,
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
