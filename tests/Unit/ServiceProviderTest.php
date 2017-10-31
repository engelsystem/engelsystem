<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

abstract class ServiceProviderTest extends TestCase
{
    /**
     * @param array $methods
     * @return Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getApp($methods = ['make', 'instance'])
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        return $this->getMockBuilder(Application::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $object
     * @param string                                  $method
     * @param array                                   $arguments
     * @param mixed                                   $return
     */
    protected function setExpects($object, $method, $arguments, $return = null)
    {
        $invocation = $object->expects($this->once())
            ->method($method);
        call_user_func_array([$invocation, 'with'], $arguments);

        if (!is_null($return)) {
            $invocation->willReturn($return);
        }
    }
}
