<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules\Stub;

class ParentClassImplementation
{
    public bool $validateResult = true;

    public mixed $lastInput;

    public function validate(mixed $input): bool
    {
        $this->lastInput = $input;

        return $this->validateResult;
    }
}
