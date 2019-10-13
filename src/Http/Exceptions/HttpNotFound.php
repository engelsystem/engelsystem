<?php

namespace Engelsystem\Http\Exceptions;

use Throwable;

class HttpNotFound extends HttpException
{
    /**
     * @param string         $message
     * @param array          $headers
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = '',
        array $headers = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(404, $message, $headers, $code, $previous);
    }
}
