<?php

namespace Engelsystem\Http\Exceptions;

class HttpTemporaryRedirect extends HttpRedirect
{
    /**
     * @param string $url
     * @param array  $headers
     */
    public function __construct(
        string $url,
        array $headers = []
    ) {
        parent::__construct($url, 302, $headers);
    }
}
