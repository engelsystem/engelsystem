<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Between as RespectBetween;

class Between extends RespectBetween
{
    use StringInputLength;
}
