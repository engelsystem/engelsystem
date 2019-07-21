<?php

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\In as RespectIn;

class In extends RespectIn
{
    /**
     * @param mixed $haystack
     * @param bool  $compareIdentical
     */
    public function __construct($haystack, $compareIdentical = false)
    {
        if (!is_array($haystack)) {
            $haystack = explode(',', $haystack);
        }

        parent::__construct($haystack, $compareIdentical);
    }
}
