<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Renderer\Renderer;
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

        if (is_int($times)) {
            $times = $this->exactly($times);
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

    /**
     * @param bool $mockImplementation
     * @return Translator&MockObject
     */
    protected function mockTranslator(bool $mockImplementation = true): Translator
    {
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['translate'])
            ->getMock();

        if ($mockImplementation) {
            $translator->method('translate')
                ->willReturnCallback(fn(string $key, array $replace = []) => $key);
        }

        $this->app->instance('translator', $translator);

        return $translator;
    }

    /**
     * @param bool $mockImplementation Whether to mock the Renderer methods
     * @return Renderer&MockObject
     */
    protected function mockRenderer(bool $mockImplementation = true): Renderer
    {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        if ($mockImplementation) {
            $renderer->method('render')
                ->willReturnCallback(fn(string $template, array $data = []) => $template . json_encode($data));
        }

        $this->app->instance('renderer', $renderer);

        return $renderer;
    }
}
