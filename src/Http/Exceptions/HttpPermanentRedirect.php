<?php

namespace Engelsystem\Http\Exceptions;

class HttpPermanentRedirect extends HttpRedirect
{
    /**
     * @param string $url
     * @param array  $headers
     */
    public function __construct(
        string $url,
        array $headers = []
    ) {
        parent::__construct($url, 301, $headers);
    }
}
