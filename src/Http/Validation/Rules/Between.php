<?php

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Between as RespectBetween;

class Between extends RespectBetween
{
    use StringInputLength;
}
