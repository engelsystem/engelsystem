<?php

namespace Engelsystem\Test\Unit\Http\Validation\Rules\Stub;

class ParentClassImplementation
{
    /** @var bool */
    public $validateResult = true;

    /** @var mixed */
    public $lastInput;

    /**
     * @return bool
     */
    public function validate(mixed $input): bool
    {
        $this->lastInput = $input;

        return $this->validateResult;
    }
}
