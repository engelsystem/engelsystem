<?php

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;

class Version
{
    protected string $versionFile = 'VERSION';

    public function __construct(protected string $storage, protected Config $config)
    {
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
