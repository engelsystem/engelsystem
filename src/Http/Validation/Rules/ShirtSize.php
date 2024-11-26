<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

/**
 * Validates whether the value is contained in the keys of the config item "tshirt_sizes".
 */
class ShirtSize extends In
{
    public function __construct()
    {
        parent::__construct(array_keys(config('tshirt_sizes')));
    }
}
