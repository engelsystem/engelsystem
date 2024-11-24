<?php

declare(strict_types=1);

namespace Engelsystem\Http\Exceptions;

use Throwable;

class HttpNotFound extends HttpException
{
    public function __construct(
        string $message = '',
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(404, $message, $headers, $code, $previous);
    }
}
