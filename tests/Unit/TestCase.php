<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    /** @var Application */
    protected $app;

    /**
     * @param MockObject      $object
     * @param string          $method
     * @param array           $arguments
     * @param mixed           $return
     * @param InvocationOrder $times
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

    /**
     * Called before each test run
     */
    protected function setUp(): void
    {
        $this->app = new Application(__DIR__ . '/../../');

        $faker = FakerFactory::create();
        $faker->addProvider(new FakerProvider($faker));
        $this->app->instance(Generator::class, $faker);
    }
}
