<?php

namespace Engelsystem\Routing;

use Engelsystem\Http\Request;

class UrlGenerator
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public static function to($path, $parameters = [])
    {
        $path = '/' . ltrim($path, '/');
        $request = Request::getInstance();
        $uri = $request->getUriForPath($path);

        if (!empty($parameters) && is_array($parameters)) {
            $parameters = http_build_query($parameters);
            $uri .= '?' . $parameters;
        }

        return $uri;
    }
}
