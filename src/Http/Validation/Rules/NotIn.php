<?php

namespace Engelsystem\Http\Validation\Rules;

class NotIn extends In
{
    /**
     * @param mixed $input
     * @return bool
     */
    public function validate($input)
    {
        return !parent::validate($input);
    }
}
