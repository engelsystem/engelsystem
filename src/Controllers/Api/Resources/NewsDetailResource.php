<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class NewsDetailResource extends NewsResource
{
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'comments' => NewsCommentResource::collection($this->model->comments)->toArray(),
        ]);
    }
}
