<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Checked extends AbstractRule
{
    public function validate(mixed $input): bool
    {
        return in_array($input, ['yes', 'on', 1, '1', 'true', true], true);
    }
}
