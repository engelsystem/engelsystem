<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigLoader;
use PHPUnit\Framework\TestCase;

class TwigLoaderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Renderer\TwigLoader::findTemplate
     */
    public function testFindTemplate(): void
    {
        $loader = new TwigLoader();

        $loader->setPaths(__DIR__);
        $realPath = __DIR__ . '/Stub/foo.twig';

        $return = $loader->findTemplate('Stub/foo.twig');
        $this->assertEquals($realPath, $return);

        $return = $loader->findTemplate('Stub/foo');
        $this->assertEquals($realPath, $return);
    }
}
