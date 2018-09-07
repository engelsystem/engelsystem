<?php

namespace Engelsystem\Http;

/**
 * To switch between different URL schemes.
 */
interface UrlGeneratorInterface
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function to($path, $parameters = []);
}
