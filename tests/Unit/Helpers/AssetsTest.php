<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Assets;
use Engelsystem\Test\Unit\TestCase;

class AssetsTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Assets::__construct
     * @covers \Engelsystem\Helpers\Assets::getAssetPath
     */
    public function testGetAssetPath(): void
    {
        $assets = new Assets('/foo/bar');
        $this->assertEquals('lorem.bar', $assets->getAssetPath('lorem.bar'));

        $assets = new Assets(__DIR__ . '/Stub/files');
        $this->assertEquals('something.xyz', $assets->getAssetPath('something.xyz'));
        $this->assertEquals('lorem-hashed.ipsum', $assets->getAssetPath('foo.bar'));
    }
}
