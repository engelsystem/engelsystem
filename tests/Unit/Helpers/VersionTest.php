<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Version;
use Engelsystem\Test\Unit\ServiceProviderTest;

class VersionTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Version::__construct
     * @covers \Engelsystem\Helpers\Version::getVersion
     */
    public function testGetVersion()
    {
        $config = new Config();
        $version = new Version(__DIR__ . '/Stub', $config);

        $this->assertEquals('n/a', $version->getVersion());

        $version = new Version(__DIR__ . '/Stub/files', $config);
        $this->assertEquals('0.42.0-testing', $version->getVersion());

        $config->set('version', '1.2.3-dev');
        $this->assertEquals('1.2.3-dev', $version->getVersion());
    }
}
