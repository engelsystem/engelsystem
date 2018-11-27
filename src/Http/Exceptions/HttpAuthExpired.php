<?php

namespace Engelsystem\Http\Exceptions;

use Throwable;

class HttpAuthExpired extends HttpException
{
    /**
     * @param string         $message
     * @param array          $headers
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Authentication Expired',
        array $headers = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        // The 419 code is used as "Page Expired" to differentiate from a 401 (not authorized)
        parent::__construct(419, $message, $headers, $code, $previous);
    }
}
