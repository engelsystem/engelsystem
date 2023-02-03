<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedirectorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Redirector::__construct
     * @covers \Engelsystem\Http\Redirector::to
     */
    public function testTo(): void
    {
        $request = new Request();
        $response = new Response();
        $url = $this->getUrlGenerator();

        $redirector = new Redirector($request, $response, $url);

        $return = $redirector->to('/test');
        $this->assertEquals(['/test'], $return->getHeader('location'));
        $this->assertEquals(302, $return->getStatusCode());

        $return = $redirector->to('/foo', 303, ['test' => 'data']);
        $this->assertEquals(['/foo'], $return->getHeader('location'));
        $this->assertEquals(303, $return->getStatusCode());
        $this->assertEquals(['data'], $return->getHeader('test'));
    }

    /**
     * @covers \Engelsystem\Http\Redirector::back
     * @covers \Engelsystem\Http\Redirector::getPreviousUrl
     */
    public function testBack(): void
    {
        $request = new Request();
        $response = new Response();
        $url = $this->getUrlGenerator();

        $redirector = new Redirector($request, $response, $url);
        $return = $redirector->back();
        $this->assertEquals(['/'], $return->getHeader('location'));
        $this->assertEquals(302, $return->getStatusCode());

        $request = $request->withHeader('referer', '/old-page');
        $redirector = new Redirector($request, $response, $url);
        $return = $redirector->back(303, ['foo' => 'bar']);
        $this->assertEquals(303, $return->getStatusCode());
        $this->assertEquals(['/old-page'], $return->getHeader('location'));
        $this->assertEquals(['bar'], $return->getHeader('foo'));
    }

    protected function getUrlGenerator(): UrlGeneratorInterface|MockObject
    {
        /** @var UrlGeneratorInterface|MockObject $url */
        $url = $this->getMockForAbstractClass(UrlGeneratorInterface::class);
        $url->expects($this->atLeastOnce())
            ->method('to')
            ->willReturnCallback([$this, 'returnPath']);

        return $url;
    }

    /**
     * Returns the provided path
     */
    public function returnPath(string $path): string
    {
        return $path;
    }
}
