<?php

namespace Engelsystem\Http\Validation\Rules;

use DateTime;
use Illuminate\Support\Str;
use Throwable;

trait StringInputLength
{
    /**
     * Use the input length of a string
     *
     * @param mixed $input
     * @return bool
     */
    public function validate($input): bool
    {
        if (
            is_string($input)
            && !is_numeric($input)
            && !$this->isDateTime($input)
        ) {
            $input = Str::length($input);
        }

        return parent::validate($input);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    protected function isDateTime($input): bool
    {
        try {
            new DateTime($input);
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }
}
