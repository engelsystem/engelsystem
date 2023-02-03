<?php

declare(strict_types=1);

namespace Engelsystem\Http;

/**
 * To switch between different URL schemes.
 */
interface UrlGeneratorInterface
{
    public function to(string $path, array $parameters = []): string;
}
