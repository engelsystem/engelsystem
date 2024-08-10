<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class After extends AbstractRule
{
    use ComparesDateTime;

    protected function compare(mixed $input): bool
    {
        return $this->orEqual ? $input >= $this->compareTo : $input > $this->compareTo;
    }
}
