<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Between as RespectBetween;
use Respect\Validation\Rules\Core\Envelope;

class Between extends Envelope
{
    use StringInputLength;

    public function __construct(mixed $minValue, mixed $maxValue)
    {
        parent::__construct(new RespectBetween($minValue, $maxValue));
    }
}
