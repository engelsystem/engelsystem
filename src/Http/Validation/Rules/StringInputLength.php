<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use DateTime;
use Exception;
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
            $inputDateTime = new DateTime($input);
            $now = new DateTime();

            // Min 1s diff to exclude any not auto-detected dates / times like ...
            return abs($inputDateTime->getTimestamp() - $now->getTimestamp()) > 1
                // Different timezone to prevent interpreting the value as a timezone which happens with H
                && $inputDateTime->getTimezone()->getName() != $input;
        } catch (Exception) {
            // Ignore it
        }

        return false;
    }
}
