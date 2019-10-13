<?php

namespace Engelsystem\Test\Unit\Http\Validation\Rules\Stub;

class ParentClassImplementation
{
    /** @var bool */
    public $validateResult = true;

    /** @var mixed */
    public $lastInput;

    /**
     * @param mixed $input
     * @return bool
     */
    public function validate($input): bool
    {
        $this->lastInput = $input;

        return $this->validateResult;
    }
}
