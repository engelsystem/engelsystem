<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Uuid;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Uuid::class, 'getFunctions')]
#[CoversMethod(Uuid::class, 'getUuid')]
#[CoversMethod(Uuid::class, 'getUuidBy')]
class UuidTest extends ExtensionTestCase
{
    public function testGetGlobals(): void
    {
        $extension = new Uuid();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('uuid', [$extension, 'getUuid'], $functions);
        $this->assertExtensionExists('uuidBy', [$extension, 'getUuidBy'], $functions);
    }

    public function testGetUuid(): void
    {
        $extension = new Uuid();

        $uuid = $extension->getUuid();
        $this->assertTrue(Str::isUuid($uuid));
    }


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
