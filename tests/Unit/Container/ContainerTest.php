<?php

namespace Engelsystem\Test\Config;

use Engelsystem\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Container\Container::get
     */
    public function testGet()
    {
        $container = new Container();
        $class = new class
        {
        };

        $container->instance('foo', $class);
        $this->assertSame($class, $container->get('foo'));
    }

    /**
     * @covers \Engelsystem\Container\Container::get
     * @expectedException \Engelsystem\Container\NotFoundException
     */
    public function testGetException()
    {
        $container = new Container();

        $container->get('not.registered.service');
    }

    /**
     * @covers \Engelsystem\Container\Container::instance
     * @covers \Engelsystem\Container\Container::resolve
     */
    public function testInstance()
    {
        $container = new Container();
        $class = new class
        {
        };

        $container->instance('foo', $class);
        $this->assertSame($class, $container->get('foo'));
    }

    /**
     * @covers \Engelsystem\Container\Container::has
     */
    public function testHas()
    {
        $container = new Container();

        $this->assertFalse($container->has('test'));

        $class = new class
        {
        };

        $container->instance('test', $class);
        $this->assertTrue($container->has('test'));
    }

    /**
     * @covers \Engelsystem\Container\Container::singleton
     */
    public function testSingleton()
    {
        $container = new Container();
        $class = new class
        {
        };

        $container->singleton('foo', $class);
        $this->assertSame($class, $container->get('foo'));
        $this->assertSame($class, $container->get('foo'));
    }

    /**
     * @covers \Engelsystem\Container\Container::setInstance
     * @covers \Engelsystem\Container\Container::getInstance
     */
    public function testContainerSingleton()
    {
        $container0 = new Container();
        $container = Container::getInstance();

        $this->assertNotSame($container0, $container);

        $container1 = new Container;
        Container::setInstance($container1);

        $this->assertSame($container1, Container::getInstance());
    }
}
