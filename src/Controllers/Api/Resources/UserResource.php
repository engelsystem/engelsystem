<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;

class UserResource extends BasicResource
{
    protected Collection | BaseModel | User $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'first_name' => $this->model->personalData->first_name,
            'last_name' => $this->model->personalData->last_name,
            'pronoun' => $this->model->personalData->pronoun,
            'contact' => $this->model->contact->only(['dect', 'mobile']),
            'url' => url('/users', ['action' => 'view', 'user_id' => $this->model->id]),
        ];
    }
}
