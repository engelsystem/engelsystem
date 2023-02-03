<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Min as RespectMin;

class Min extends RespectMin
{
    use StringInputLength;
}
