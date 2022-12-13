<?php

namespace Engelsystem\Http\Validation\Rules;

class NotIn extends In
{
    /**
     * @return bool
     */
    public function validate(mixed $input)
    {
        return !parent::validate($input);
    }
}
