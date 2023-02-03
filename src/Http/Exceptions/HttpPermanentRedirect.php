<?php

declare(strict_types=1);

namespace Engelsystem\Http\Exceptions;

class HttpPermanentRedirect extends HttpRedirect
{
    public function __construct(
        string $url,
        array $headers = []
    ) {
        parent::__construct($url, 301, $headers);
    }
}
