<?php

namespace Engelsystem\Test\Config;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApplicationTest extends TestCase
{
    /**
     * @covers       \Engelsystem\Application::__construct
     * @covers       \Engelsystem\Application::registerBaseBindings
     */
    public function testConstructor()
    {
        $app = new Application();

        $this->assertInstanceOf(Container::class, $app);
        $this->assertInstanceOf(ContainerInterface::class, $app);
        $this->assertSame($app, $app->get('app'));
        $this->assertSame($app, $app->get('container'));
        $this->assertSame($app, $app->get(Container::class));
        $this->assertSame($app, $app->get(Application::class));
        $this->assertSame($app, $app->get(ContainerInterface::class));
        $this->assertSame($app, Container::getInstance());
    }
}
