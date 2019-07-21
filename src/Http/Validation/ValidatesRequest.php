<?php

namespace Engelsystem\Http\Validation;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;

trait ValidatesRequest
{
    /** @var Validator */
    protected $validator;

    /**
     * @param Request $request
     * @param array   $rules
     * @return array
     */
    protected function validate(Request $request, array $rules)
    {
        if (!$this->validator->validate(
            (array)$request->getParsedBody(),
            $rules
        )) {
            throw new ValidationException($this->validator);
        }

        return $this->validator->getData();
    }

    /**
     * @param Validator $validator
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }
}
