<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Config\Config;
use Engelsystem\Renderer\Twig\Extensions\Develop;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\VarDumper\VarDumper;

class DevelopTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Develop::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Develop::getFunctions
     */
    public function testGetGlobals(): void
    {
        $config = new Config();
        $extension = new Develop($config);

        $functions = $extension->getFunctions();
        $this->assertEquals([], $functions);

        $config->set('environment', 'development');
        $functions = $extension->getFunctions();
        $this->assertExtensionExists('dump', [$extension, 'dump'], $functions);
        $this->assertExtensionExists('dd', [$extension, 'dd'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Develop::dump
     * @covers \Engelsystem\Renderer\Twig\Extensions\Develop::setDumper
     */
    public function testDump(): void
    {
        $config = new Config();
        $varDumper = new VarDumper();
        $varDumper->setHandler(function ($var): void {
            echo $var;
        });

        $extension = new Develop($config);
        $extension->setDumper($varDumper);

        $return = $extension->dump('Foo', 1234);
        $this->assertEquals('Foo1234', $return);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Develop::dd
     */
    public function testDD(): void
    {
        /** @var Develop|MockObject $extension */
        $extension = $this->getMockBuilder(Develop::class)
            ->onlyMethods(['exit', 'flushBuffers', 'dump'])
            ->disableOriginalConstructor()
            ->getMock();
        $extension->expects($this->once())
            ->method('exit');
        $extension->expects($this->once())
            ->method('flushBuffers');
        $extension->expects($this->once())
            ->method('dump')
            ->with(123, 'Abc');

        $return = $extension->dd(123, 'Abc');
        $this->assertEquals('', $return);
    }
}
