<?php

namespace Engelsystem\Test\Unit\Http\Validation\Stub;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;

class ValidatesRequestImplementation extends BaseController
{
    public function validateData(Request $request, array $rules): array
    {
        return $this->validate($request, $rules);
    }

    public function hasValidator(): bool
    {
        return !is_null($this->validator);
    }
}
