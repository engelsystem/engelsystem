<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Config\Config;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\AddHeaders;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(AddHeaders::class, '__construct')]
#[CoversMethod(AddHeaders::class, 'process')]
class AddHeadersTest extends TestCase
{
    public function testRegister(): void
    {
        $request = $this->getStubBuilder(ServerRequestInterface::class)->getStub();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = new Response();

        $handler->expects($this->atLeastOnce())
            ->method('handle')
            ->willReturn($response);

        $config = new Config(['add_headers' => false]);

        $middleware = new AddHeaders($config);
        $this->assertEquals($response, $middleware->process($request, $handler));

        $config->set('add_headers', true);
        $config->set('headers', ['Foo-Header' => 'bar!']);
        $return = $middleware->process($request, $handler);

        $this->assertNotEquals($response, $return);
        $this->assertEquals(['bar!'], $return->getHeaders()['Foo-Header']);
    }
}
