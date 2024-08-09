<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Illuminate\Contracts\Support\Arrayable;

class ShiftWithEntriesResource extends ShiftResource
{
    public function toArray(array | Arrayable $location = [], array | Arrayable $angelTypes = []): array
    {
        return [
            ...parent::toArray($location),
            'needed_angel_types' => $angelTypes instanceof Arrayable ? $angelTypes->toArray() : $angelTypes,
        ];
    }
}
