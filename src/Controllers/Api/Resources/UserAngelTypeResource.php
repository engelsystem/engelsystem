<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\AngelType;

class UserAngelTypeResource extends AngelTypeResource
{
    public function toArray(): array
    {
        /** @var AngelType $angelType */
        $angelType = $this->model;
        /** @var AngelType $angelType */
        $userAngelType = $this->model->pivot;

        return [
            'angeltype' => AngelTypeResource::toIdentifierArray($angelType),
            'confirmed' => !$angelType->restricted
                || $userAngelType->supporter
                || $userAngelType->confirm_user_id,
            'supporter' => $userAngelType->supporter,
        ];
    }
}
