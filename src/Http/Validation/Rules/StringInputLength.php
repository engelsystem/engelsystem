<?php

namespace Engelsystem\Http\Validation\Rules;

use Engelsystem\Helpers\Carbon;
use Illuminate\Support\Str;
use Throwable;

trait StringInputLength
{
    /**
     * Use the input length of a string
     */
    public function validate(mixed $input): bool
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

    protected function isDateTime(mixed $input): bool
    {
        try {
            $date = Carbon::make($input);
        } catch (Throwable $e) {
            return false;
        }

        return !is_null($date);
    }
}
