<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Uuid;
use Illuminate\Support\Str;

class UuidTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Uuid::getFunctions
     */
    public function testGetGlobals(): void
    {
        $extension = new Uuid();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('uuid', [$extension, 'getUuid'], $functions);
        $this->assertExtensionExists('uuidBy', [$extension, 'getUuidBy'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Uuid::getUuid
     */
    public function testGetUuid(): void
    {
        $extension = new Uuid();

        $uuid = $extension->getUuid();
        $this->assertTrue(Str::isUuid($uuid));
    }


    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Uuid::getUuidBy
     */
    public function testGetUuidBy(): void
    {
        $extension = new Uuid();

        $uuid = $extension->getUuidBy('test');
        $this->assertTrue(Str::isUuid($uuid));
        $this->assertEquals('098f6bcd-4621-4373-8ade-4e832627b4f6', $uuid);

        $uuid = $extension->getUuidBy('test', '1337');
        $this->assertTrue(Str::isUuid($uuid));
        $this->assertStringStartsWith('1337', $uuid);
    }
}
