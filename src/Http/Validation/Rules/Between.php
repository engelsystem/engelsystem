<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\AbstractEnvelope;
use Respect\Validation\Rules\Between as RespectBetween;

class Between extends AbstractEnvelope
{
    use StringInputLength;

    public function __construct(mixed $minValue, mixed $maxValue)
    {
        parent::__construct(new RespectBetween($minValue, $maxValue));
    }
}
