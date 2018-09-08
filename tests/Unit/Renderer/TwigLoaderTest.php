<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\TwigLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass as Reflection;

class TwigLoaderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Renderer\TwigLoader::findTemplate
     */
    public function testFindTemplate()
    {
        $loader = new TwigLoader();

        $reflection = new Reflection(get_class($loader));
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);

        $realPath = __DIR__ . '/Stub/foo.twig';
        $property->setValue($loader, ['Stub/foo.twig' => $realPath]);

        $return = $loader->findTemplate('Stub/foo.twig');
        $this->assertEquals($realPath, $return);

        $return = $loader->findTemplate('Stub/foo');
        $this->assertEquals($realPath, $return);
    }
}
