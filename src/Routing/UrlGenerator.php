<?php

namespace Engelsystem\Routing;

class UrlGenerator
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function to($path, $parameters = [])
    {
        $path = '/' . ltrim($path, '/');
        $request = app('request');
        $uri = $request->getUriForPath($path);

        if (!empty($parameters) && is_array($parameters)) {
            $parameters = http_build_query($parameters);
            $uri .= '?' . $parameters;
        }

        return $uri;
    }
}
