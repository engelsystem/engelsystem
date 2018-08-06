<?php

namespace Engelsystem\Routing;

interface UrlGeneratorInterface
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function to($path, $parameters = []);
}
