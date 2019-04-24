<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ServiceProviderTest extends TestCase
{
    /**
     * @param array $methods
     * @return Application|MockObject
     */
    protected function getApp($methods = ['make', 'instance'])
    {
        return $this->getMockBuilder(Application::class)
            ->setMethods($methods)
            ->getMock();
    }
}
