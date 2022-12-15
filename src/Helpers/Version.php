<?php

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;

class Version
{
    protected Config $config;

    protected string $storage;

    protected string $versionFile = 'VERSION';

    public function __construct(string $storage, Config $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    public function getVersion(): string
    {
        $file = $this->storage . DIRECTORY_SEPARATOR . $this->versionFile;

        $version = 'n/a';
        if (file_exists($file)) {
            $version = trim(file_get_contents($file));
        }

        return $this->config->get('version', $version);
    }
}
