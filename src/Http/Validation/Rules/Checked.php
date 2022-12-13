<?php

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Checked extends AbstractRule
{
    public function validate(mixed $input)
    {
        return in_array($input, ['yes', 'on', 1, '1', 'true', true], true);
    }
}
