<?php

namespace Engelsystem\Http;

/**
 * Provides urls when rewriting on the webserver is enabled. (default)
 *
 * The urls have the form <app url>/<path>?<parameters>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string url in the form [app url]/[path]?[parameters]
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
