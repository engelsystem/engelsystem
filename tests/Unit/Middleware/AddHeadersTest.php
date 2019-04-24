<?php

namespace Engelsystem\Test\Unit\Middleware;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Config\Config;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\AddHeaders;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddHeadersTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Middleware\AddHeaders::__construct
     * @covers \Engelsystem\Middleware\AddHeaders::process
     */
    public function testRegister()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
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
        $this->assertArraySubset(['Foo-Header' => ['bar!']], $return->getHeaders());
    }
}
