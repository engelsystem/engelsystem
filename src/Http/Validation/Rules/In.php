<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Respect\Validation\Rules\Core\Envelope;
use Respect\Validation\Rules\In as RespectIn;

class In extends Envelope
{
    public function __construct(mixed $haystack, bool $compareIdentical = false)
    {
        if (!is_array($haystack)) {
            $haystack = explode(',', $haystack);
        }

        parent::__construct(new RespectIn($haystack, $compareIdentical));
    }
}
