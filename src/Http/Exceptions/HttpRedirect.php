<?php

namespace Engelsystem\Http\Exceptions;

class HttpRedirect extends HttpException
{
    public function __construct(
        string $url,
        int $statusCode = 302,
        array $headers = []
    ) {
        $headers = array_merge([
            'Location' => $url,
        ], $headers);

        parent::__construct($statusCode, '', $headers);
    }
}
