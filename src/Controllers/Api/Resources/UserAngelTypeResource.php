<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class UserAngelTypeResource extends AngelTypeResource
{
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'confirmed' => !$this->model->restricted
                || $this->model->pivot->supporter
                || $this->model->pivot->confirm_user_id,
            'supporter' => $this->model->pivot->supporter,
        ];
    }
}
