<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ServiceProviderTest extends TestCase
{
    protected function getApp(array $methods = ['make', 'instance']): Application|MockObject
    {
        return $this->getMockBuilder(Application::class)
            ->onlyMethods($methods)
            ->getMock();
    }
}
