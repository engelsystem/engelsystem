<?php

namespace Engelsystem\Test\Unit\Http\Validation\Stub;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;

class ValidatesRequestImplementation extends BaseController
{
    /**
     * @param Request $request
     * @param array   $rules
     * @return array
     */
    public function validateData(Request $request, array $rules)
    {
        return $this->validate($request, $rules);
    }

    /**
     * @return bool
     */
    public function hasValidator()
    {
        return !is_null($this->validator);
    }
}
