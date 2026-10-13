<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Message;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class MessageResource extends BasicResource
{
    protected Collection | BaseModel | Pivot | Message $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'text' => $this->model->text,
            'sender' => UserResource::toIdentifierArray($this->model->sender),
            'receiver' => UserResource::toIdentifierArray($this->model->receiver),
            'read' => (bool) $this->model->read,
            'created_at' => $this->model->created_at,
            'updated_at' => $this->model->updated_at,
        ];
    }
}
