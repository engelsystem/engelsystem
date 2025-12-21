<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

class InMany extends In
{
    public function validate(mixed $input): bool
    {
        foreach ((array) $input as $value) {
            if (!parent::validate($value)) {
                return false;
            }
        }

        return true;
    }
}
