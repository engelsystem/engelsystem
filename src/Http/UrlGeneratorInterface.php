<?php

namespace Engelsystem\Http;

/**
 * To switch between different URL schemes.
 */
interface UrlGeneratorInterface
{
    /**
     * @param array  $parameters
     */
    public function to(string $path, array $parameters = []): string;
}
