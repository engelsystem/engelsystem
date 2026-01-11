<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Assets;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Assets::class, '__construct')]
#[CoversMethod(Assets::class, 'getAssetPath')]
class AssetsTest extends TestCase
{
    public function testGetAssetPath(): void
    {
        $assets = new Assets('/foo/bar');
        $this->assertEquals('lorem.bar', $assets->getAssetPath('lorem.bar'));

        $assets = new Assets(__DIR__ . '/Stub/files');
        $this->assertEquals('something.xyz', $assets->getAssetPath('something.xyz'));
        $this->assertEquals('lorem-hashed.ipsum', $assets->getAssetPath('foo.bar'));
    }
}
