<?php

declare(strict_types=1);

namespace Engelsystem\Http\Exceptions;

use Engelsystem\Http\Validation\Validator;
use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException
{
    public function __construct(
        protected Validator $validator,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
