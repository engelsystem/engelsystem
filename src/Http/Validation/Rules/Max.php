<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Max as RespectMax;

class Max extends RespectMax
{
    use StringInputLength;
}
