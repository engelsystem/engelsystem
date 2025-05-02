<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;

class UserAngelTypeReferenceResource extends BasicResource
{
    public function toArray(): array
    {
        /** @var User $user */
        $user = $this->model->pivotRelated;
        /** @var AngelType $angelType */
        $angelType = $this->model->pivotParent;
        /** @var UserAngelType $userAngelType */
        $userAngelType = $this->model;

        return [
            'user' => UserResource::toIdentifierArray($user),
            'confirmed' => !$angelType->restricted
                || $userAngelType->supporter
                || $userAngelType->confirm_user_id,
            'supporter' => $userAngelType->supporter,
        ];
    }
}
