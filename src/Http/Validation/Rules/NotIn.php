<?php

namespace Engelsystem\Http\Validation\Rules;

class NotIn extends In
{
    public function validate(mixed $input): bool
    {
        return !parent::validate($input);
    }
}
