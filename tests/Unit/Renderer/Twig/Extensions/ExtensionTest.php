<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig_Function as TwigFunction;
use Twig_Node as TwigNode;

abstract class ExtensionTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * Assert that a twig filter was registered
     *
     * @param string         $name
     * @param callable       $callback
     * @param TwigFunction[] $functions
     */
    protected function assertFilterExists($name, $callback, $functions)
    {
        foreach ($functions as $function) {
            if ($function->getName() != $name) {
                continue;
            }

            $this->assertEquals($callback, $function->getCallable());
            return;
        }

        $this->fail(sprintf('Filter %s not found', $name));
    }

    /**
     * Assert that a twig function was registered
     *
     * @param string         $name
     * @param callable       $callback
     * @param TwigFunction[] $functions
     * @param array $options
     */
    protected function assertExtensionExists($name, $callback, $functions, $options = [])
    {
        foreach ($functions as $function) {
            if ($function->getName() != $name) {
                continue;
            }

            $this->assertEquals($callback, $function->getCallable());

            if (isset($options['is_save'])) {
                /** @var TwigNode|MockObject $twigNode */
                $twigNode = $this->createMock(TwigNode::class);

                $this->assertArraySubset($options['is_save'], $function->getSafe($twigNode));
            }

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

    /**
     * Assert that a token parser was set
     *
     * @param $tokenParser
     * @param $tokenParsers
     */
    protected function assertTokenParserExists($tokenParser, $tokenParsers)
    {
        $this->assertArraySubset(
            [$tokenParser],
            $tokenParsers,
            sprintf('Token parser %s not found', get_class($tokenParser))
        );
    }
}
