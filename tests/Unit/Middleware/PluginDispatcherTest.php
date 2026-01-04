<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\PluginDispatcher;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\Middleware;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(PluginDispatcher::class, '__construct')]
class PluginDispatcherTest extends TestCase
{
    public function testConstruct(): void
    {
        $handler = $this->createStub(RequestHandlerInterface::class);
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
