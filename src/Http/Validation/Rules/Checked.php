<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Checked extends AbstractRule
{
    use Truthy;

    public function validate(mixed $input): bool
    {
        return $this->truthy($input);
    }
}
