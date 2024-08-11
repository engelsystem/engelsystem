<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

trait Truthy
{
    protected function truthy(mixed $value): bool
    {
        return in_array($value, ['yes', 'on', 1, '1', 'true', true], true);
    }
}
