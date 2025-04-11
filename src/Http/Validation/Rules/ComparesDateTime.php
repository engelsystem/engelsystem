<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation\Rules;

use Engelsystem\Helpers\Carbon;

trait ComparesDateTime
{
    use Truthy;

    protected mixed $compareTo;
    protected bool $orEqual;

    public function __construct(mixed $compareTo, mixed $orEqual = false)
    {
        $this->orEqual = $this->truthy($orEqual);
        $this->compareTo = $this->toDateTime($compareTo);
    }

    public function validate(mixed $input): bool
    {
        $input = $this->toDateTime($input);

        return $this->compare($input, $this->compareTo);
    }

    protected function toDateTime(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = Carbon::make($value);
        }

        return $value;
    }
}
