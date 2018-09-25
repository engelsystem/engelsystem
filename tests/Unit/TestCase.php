<?php

namespace Engelsystem\Test\Unit;

use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

abstract class TestCase extends PHPUnitTestCase
{
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
