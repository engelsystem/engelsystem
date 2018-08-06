<?php

namespace Engelsystem\Routing;

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
    public function link_to($path, $parameters = []);
}
