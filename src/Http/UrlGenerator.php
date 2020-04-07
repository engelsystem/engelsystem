<?php

namespace Engelsystem\Http;

/**
 * Provides URLs
 *
 * The urls have the form <app url>/<path>?<parameters>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * Create a URL for the given path, using the applications base url if configured
     *
     * @param string $path
     * @param array  $parameters
     * @return string url in the form [app url]/[path]?[parameters]
     */
    public function to(string $path, array $parameters = []): string
    {
        $uri = $path;

        if (!$this->isValidUrl($uri)) {
            $uri = $this->generateUrl($path);
        }

        if (!empty($parameters) && is_array($parameters)) {
            $parameters = http_build_query($parameters);
            $uri .= '?' . $parameters;
        }

        return $uri;
    }

    /**
     * Check if the URL is valid
     *
     * @param string $path
     * @return bool
     */
    public function isValidUrl(string $path): bool
    {
        return preg_match('~^(?:\w+:(//)?|#)~', $path) || filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Prepend the auto detected or configured app base path and domain
     *
     * @param $path
     * @return string
     */
    protected function generateUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        $baseUrl = config('url');
        if ($baseUrl) {
            $uri = rtrim($baseUrl, '/') . $path;
        } else {
            /** @var Request $request */
            $request = app('request');
            $uri = $request->getUriForPath($path);
        }

        return $uri;
    }
}
