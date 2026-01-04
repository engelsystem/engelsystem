<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\PluginDispatcher;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\Middleware;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Server\RequestHandlerInterface;

class PluginDispatcherTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\PluginDispatcher::__construct
     */
    public function testConstruct(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $request = new Request();
        $response = new Response();
        $pluginMiddleware = new Middleware($response);
        $this->app->instance('m', $pluginMiddleware);
        $this->app->tag('m', 'plugin.middleware');

        $middleware = new PluginDispatcher($this->app);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
        $this->assertTrue($pluginMiddleware->processed);
    }
}
