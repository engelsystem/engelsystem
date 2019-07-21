<?php

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;

class Version
{
    /** @var Config */
    protected $config;

    /** @vat string */
    protected $storage;

    /** @var string */
    protected $versionFile = 'VERSION';

    /**
     * @param string $storage
     * @param Config $config
     */
    public function __construct(string $storage, Config $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $file = $this->storage . DIRECTORY_SEPARATOR . $this->versionFile;

        $version = 'n/a';
        if (file_exists($file)) {
            $version = trim(file_get_contents($file));
        }

        return $this->config->get('version', $version);
    }
}
