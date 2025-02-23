<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Core\Envelope;
use Respect\Validation\Rules\Max as RespectMax;

class Max extends Envelope
{
    use StringInputLength;

    public function __construct(mixed $maxValue)
    {
        parent::__construct(new RespectMax($maxValue));
    }
}
