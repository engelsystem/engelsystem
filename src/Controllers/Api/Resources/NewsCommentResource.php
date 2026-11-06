<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\NewsComment;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class NewsCommentResource extends BasicResource
{
    protected Collection | BaseModel | Pivot | NewsComment $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'text' => $this->model->text,
            'user' => UserResource::toIdentifierArray($this->model->user),
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
        ];
    }
}
