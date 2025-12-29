<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Core\Simple;

class Checked extends Simple
{
    public function isValid(mixed $input): bool
    {
        return $input && $input !== 'false' && $input !== 'off' && $input !== 'no';
    }
}
