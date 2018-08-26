<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use PHPUnit\Framework\TestCase;
use Twig_Function as TwigFunction;

abstract class ExtensionTest extends TestCase
{
    /**
     * Assert that a twig function was registered
     *
     * @param string         $name
     * @param callable       $callback
     * @param TwigFunction[] $functions
     */
    protected function assertExtensionExists($name, $callback, $functions)
    {
        foreach ($functions as $function) {
            if ($function->getName() != $name) {
                continue;
            }

            $this->assertEquals($callback, $function->getCallable());
            return;
        }

        $this->fail(sprintf('Function %s not found', $name));
    }

    /**
     * Assert that a global exists
     *
     * @param string  $name
     * @param mixed   $value
     * @param mixed[] $globals
     */
    protected function assertGlobalsExists($name, $value, $globals)
    {
        if (isset($globals[$name])) {
            $this->assertArraySubset([$name => $value], $globals);

            return;
        }

        $this->fail(sprintf('Global %s not found', $name));
    }
}
