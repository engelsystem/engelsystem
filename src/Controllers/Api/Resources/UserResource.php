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
        $data = [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'first_name' => $this->model->personalData->first_name,
            'last_name' => $this->model->personalData->last_name,
            'pronoun' => $this->model->personalData->pronoun,
            'contact' => $this->model->contact->only(['dect', 'mobile']),
            'url' => url('/users', ['action' => 'view', 'user_id' => $this->model->id]),
        ];

        // if current user has permission: inject got_voucher and got_goodie
        $currentUser = auth()->user();
        if($currentUser->privileges->where('name', 'voucher.edit')->first() || $currentUser->id === $this->model->id) {
            $data['got_voucher'] = $this->model->state->got_voucher;
        }
        if($currentUser->privileges->where('name', 'user.goodie.edit')->first() || $currentUser->id === $this->model->id) {
            $data['got_goodie'] = $this->model->state->got_goodie;
        }

        return $data;
    }
}
