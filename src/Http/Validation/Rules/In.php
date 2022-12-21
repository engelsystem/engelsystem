<?php

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\In as RespectIn;

class In extends RespectIn
{
    public function __construct(mixed $haystack, bool $compareIdentical = false)
    {
        if (!is_array($haystack)) {
            $haystack = explode(',', $haystack);
        }

        parent::__construct($haystack, $compareIdentical);
    }
}
