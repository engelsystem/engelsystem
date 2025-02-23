<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Core\Envelope;
use Respect\Validation\Rules\DateTime as RespectDateTime;

class DateTime extends Envelope
{
    public function __construct(?string $format = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d\TH:i';
        }

        parent::__construct(new RespectDateTime($format));
    }
}
