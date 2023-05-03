<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ServiceProviderTest extends TestCase
{
    protected function getApp(array $methods = ['make', 'instance']): Application|MockObject
    {
        return $this->getMockBuilder(Application::class)
            ->onlyMethods($methods)
            ->getMock();
    }

    /**
     * Creates an Application instance with a Config set as 'config'.
     * Also sets up the instance as global Application instance.
     *
     * @param string[] $methods Names of the methods to mock
     */
    protected function createAndSetUpAppWithConfig(array $methods = ['make', 'instance']): Application|MockObject
    {
        $app = $this->getApp($methods);
        $app->instance('config', new Config([]));
        Application::setInstance($app);
        return $app;
    }
}
