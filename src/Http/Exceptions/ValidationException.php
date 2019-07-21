<?php

namespace Engelsystem\Http\Exceptions;

use Engelsystem\Http\Validation\Validator;
use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException
{
    /** @var Validator */
    protected $validator;

    /**
     * @param Validator      $validator
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        Validator $validator,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->validator = $validator;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
