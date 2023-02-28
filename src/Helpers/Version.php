<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;
use Illuminate\Support\Str;

class Version
{
    public function __construct(protected string $gitRoot, protected string $storage, protected Config $config)
    {
    }

    public function getVersion(): string
    {
        $file = $this->storage . DIRECTORY_SEPARATOR . 'VERSION';
        $gitHead = implode(DIRECTORY_SEPARATOR, [$this->gitRoot, 'logs', 'HEAD']);

        $version = 'n/a';
        if (file_exists($file)) {
            $version = trim(file_get_contents($file));
        } elseif (file_exists($gitHead)) {
            $lines = file($gitHead) ?: [];
            $lastLine = array_pop($lines) ?: '';
            $words = explode(' ', $lastLine);
            $version = Str::substr($words[1] ?? '', 0, 7);
        }

        return $this->config->get('version', $version);
    }
}
