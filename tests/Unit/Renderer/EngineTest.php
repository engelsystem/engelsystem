<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\Engine;
use Engelsystem\Test\Unit\Renderer\Stub\EngineImplementation;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Engine::class, 'share')]
class EngineTest extends TestCase
{
    public function testShare(): void
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
