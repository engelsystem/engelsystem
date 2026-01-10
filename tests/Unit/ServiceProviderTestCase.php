<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

abstract class ServiceProviderTestCase extends TestCase
{
    /**
     * @param list<non-empty-string> $methods
     */
    protected function getAppMock(array $methods = ['make', 'instance']): Application&MockObject
    {
        return $this->getMockBuilder(Application::class)
            ->onlyMethods($methods)
            ->getMock();
    }

    /**
     * @param list<non-empty-string> $methods
     */
    protected function getAppStub(array $methods = ['make', 'instance']): Application&Stub
    {
        return $this->getStubBuilder(Application::class)
            ->onlyMethods($methods)
            ->getStub();
    }

    /**
     * Creates an Application instance with a Config set as 'config'.
     * Also sets up the instance as global Application instance.
     *
     * @param list<non-empty-string> $methods Names of the methods to mock
     */
    protected function createAndSetUpAppWithConfig(array $methods = ['make', 'instance']): Application&Stub
    {
        $app = $this->getAppStub($methods);
        $app->instance('config', new Config([]));
        Application::setInstance($app);
        return $app;
    }
}
