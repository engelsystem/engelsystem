<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Test\Unit\TestCase;
use Exception;
use Twig\Node\Node as TwigNode;
use Twig\TwigFilter;
use Twig\TwigFunction;

abstract class ExtensionTestCase extends TestCase
{
    /**
     * Assert that a twig filter was registered
     *
     * @param TwigFilter[] $functions
     */
    protected function assertFilterExists(string $name, mixed $callback, array $functions): void
    {
        foreach ($functions as $function) {
            if ($function->getName() != $name) {
                continue;
            }

            if (!is_null($callback)) {
                $this->assertEquals($callback, $function->getCallable());
            } else {
                $this->assertIsCallable($function->getCallable());
            }
            return;
        }

        $this->fail(sprintf('Filter %s not found', $name));
    }

    /**
     * Assert that a twig function or filter was registered
     *
     * @param TwigFunction[]|TwigFilter[] $functions
     * @throws Exception
     */
    protected function assertExtensionExists(
        string $name,
        mixed $callback,
        array $functions,
        array $options = []
    ): void {
        foreach ($functions as $function) {
            if ($function->getName() != $name) {
                continue;
            }

            if (!is_null($callback)) {
                $this->assertEquals($callback, $function->getCallable());
            } else {
                $this->assertIsCallable($function->getCallable());
            }

            if (isset($options['is_save'])) {
                $twigNode = $this->createMock(TwigNode::class);

                $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
                    $options['is_save'],
                    $function->getSafe($twigNode),
                    array_keys($options['is_save']),
                );
            }

            return;
        }

        $this->fail(sprintf('Function %s not found', $name));
    }

    /**
     * Assert that a global exists
     *
     * @param mixed[] $globals
     * @throws Exception
     */
    protected function assertGlobalsExists(string $name, mixed $value, array $globals): void
    {
        if (array_key_exists($name, $globals)) {
            $this->assertEquals($value, $globals[$name]);

            return;
        }

        $this->fail(sprintf('Global %s not found', $name));
    }
}
