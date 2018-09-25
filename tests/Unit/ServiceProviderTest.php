<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

abstract class ServiceProviderTest extends TestCase
{
    /**
     * @param array $methods
     * @return Application|MockObject
     */
    protected function getApp($methods = ['make', 'instance'])
    {
        /** @var MockObject|Application $app */
        return $this->getMockBuilder(Application::class)
            ->setMethods($methods)
            ->getMock();
    }
}
