<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

class UserDetailResource extends UserResource
{
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'email' => $this->model->contact->email ?: $this->model->email,
            'tshirt_size' => $this->model->personalData->shirt_size,
            'language' => $this->model->settings->language,
            'arrived' => $this->model->state->arrived,
            'dates' => [
                'planned_arrival' => $this->model->personalData->planned_arrival_date,
                'planned_departure' => $this->model->personalData->planned_departure_date,
                'arrival' => $this->model->state->arrival_date,
            ],
        ]);
    }
}
