<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class AngelTypeResource extends BasicResource
{
    protected Collection | BaseModel | Pivot | AngelType $model;

    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'description' => $this->model->description,
            'restricted' => $this->model->restricted,
            'requires_driver_license' => $this->model->requires_driver_license,
            'requires_ifsg_certificate' => $this->model->requires_ifsg_certificate,
            'shift_self_signup' => $this->model->shift_self_signup,
            'contact' => [
                'name' => $this->model->contact_name,
                'email' => $this->model->contact_email,
                'dect' => $this->model->contact_dect,
            ],
            'url' => url('/angeltypes', ['action' => 'view', 'angeltype_id' => $this->model->id]),
        ];
    }
}
