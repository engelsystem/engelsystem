<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Illuminate\Contracts\Support\Arrayable;

class ShiftWithEntriesResource extends ShiftResource
{
    public function toArray(array | Arrayable $location = [], array | Arrayable $entries = []): array
    {
        return [
            ...parent::toArray($location),
            'entries' => $entries instanceof Arrayable ? $entries->toArray() : $entries,
        ];
    }
}
