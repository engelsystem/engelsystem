<?php

declare(strict_types=1);

namespace Engelsystem\Http\Validation;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;

trait ValidatesRequest
{
    protected Validator $validator;

    protected function validate(Request $request, array $rules): array
    {
        $isValid = $this->validator->validate(
            (array) $request->getParsedBody(),
            $rules
        );

        if (!$isValid) {
            throw new ValidationException($this->validator);
        }

        return $this->validator->getData();
    }

    public function setValidator(Validator $validator): void
    {
        $this->validator = $validator;
    }
}
