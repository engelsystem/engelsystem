<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Carbon\Exceptions\InvalidFormatException;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\CarbonDay;
use Illuminate\Support\Str;

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
            return !is_null(Carbon::createFromDatetime($input)) || !is_null(CarbonDay::createFromDay($input));
        } catch (InvalidFormatException) {
        }

        return false;
    }
}
