<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Config\Config as EngelsystemConfig;
use Engelsystem\Renderer\Twig\Extensions\Config;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Config::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Config::getFunctions
     */
    public function testGetFunctions()
    {
        /** @var EngelsystemConfig|MockObject $config */
        $config = $this->createMock(EngelsystemConfig::class);

        $extension = new Config($config);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('config', [$config, 'get'], $functions);
    }
}
