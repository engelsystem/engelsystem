<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Date;

class DateTime extends Date
{
    public function __construct(?string $format = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d\TH:i';
        }

        parent::__construct($format);
    }
}
