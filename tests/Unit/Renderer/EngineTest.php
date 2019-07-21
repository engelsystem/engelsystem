<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Test\Unit\Renderer\Stub\EngineImplementation;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    /**
     * @covers \Engelsystem\Renderer\Engine::share
     */
    public function testShare()
    {
        $engine = new EngineImplementation();
        $engine->share(['foo' => ['bar' => 'baz', 'lorem' => 'ipsum']]);
        $engine->share(['foo' => ['lorem' => 'dolor']]);
        $engine->share('key', 'value');

        $this->assertEquals(
            ['foo' => ['bar' => 'baz', 'lorem' => 'dolor'], 'key' => 'value'],
            $engine->getSharedData()
        );
    }
}
