<?php

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Min as RespectMin;

class Min extends RespectMin
{
    use StringInputLength;
}
