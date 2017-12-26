<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use PHPUnit\Framework\TestCase;
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

    /**
     * @param MockObject      $object
     * @param string          $method
     * @param array           $arguments
     * @param mixed           $return
     * @param InvokedRecorder $times
     */
    protected function setExpects($object, $method, $arguments = null, $return = null, $times = null)
    {
        if (is_null($times)) {
            $times = $this->once();
        }

        $invocation = $object->expects($times)
            ->method($method);

        if (is_null($arguments)) {
            $invocation->withAnyParameters();
        } else {
            call_user_func_array([$invocation, 'with'], $arguments);
        }

        if (!is_null($return)) {
            $invocation->willReturn($return);
        }
    }
}
