<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Core\Envelope;
use Respect\Validation\Rules\Min as RespectMin;

class Min extends Envelope
{
    use StringInputLength;

    public function __construct(mixed $minValue)
    {
        parent::__construct(new RespectMin($minValue));
    }
}
