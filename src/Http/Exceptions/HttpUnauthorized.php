<?php

declare(strict_types=1);

namespace Engelsystem\Http\Exceptions;

use Throwable;

class HttpUnauthorized extends HttpException
{
    public function __construct(
        string $message = '',
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(401, $message, $headers, $code, $previous);
    }
}
