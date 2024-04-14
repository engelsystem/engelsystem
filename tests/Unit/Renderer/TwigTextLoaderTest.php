<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigTextLoader;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError as TwigLoaderError;

class TwigTextLoaderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Renderer\TwigTextLoader::findTemplate
     */
    public function testFindTemplate(): void
    {
        $loader = new TwigTextLoader();

        $loader->setPaths(__DIR__);
        $realPath = __DIR__ . '/Stub/bar.text.twig';

        $return = $loader->findTemplate('Stub/bar.text.twig');
        $this->assertEquals($realPath, $return);

        $return = $loader->findTemplate('Stub/bar.text');
        $this->assertEquals($realPath, $return);

        $return = $loader->findTemplate('Stub/bar');
        $this->assertEquals($realPath, $return);

        $this->expectException(TwigLoaderError::class);
        $loader->findTemplate('Stub/foo.twig');
    }
}
