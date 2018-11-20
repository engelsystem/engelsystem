<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\ErrorHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddlewareHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig_LoaderInterface as TwigLoader;

class ErrorHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::__construct
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     * @covers \Engelsystem\Middleware\ErrorHandler::selectView
     */
    public function testProcess()
    {
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($psrResponse);

        $psrResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(505);
        $psrResponse->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([]);

        $errorHandler = new ErrorHandler($twigLoader);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($psrResponse, $return, 'Plain PSR-7 Response should be passed directly');

        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);

        $response->expects($this->exactly(4))
            ->method('getStatusCode')
            ->willReturnOnConsecutiveCalls(
                200,
                418,
                505,
                505
            );
        $response->expects($this->exactly(4))
            ->method('getHeader')
            ->willReturnOnConsecutiveCalls(
                [],
                [],
                [],
                ['application/json']
            );

        $returnResponseHandler->setResponse($response);
        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return, 'Only Responses >= 400 should be processed');

        $twigLoader->expects($this->exactly(4))
            ->method('exists')
            ->withConsecutive(
                ['errors/418'],
                ['errors/4'],
                ['errors/400'],
                ['errors/505']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false,
                true
            );

        $response->expects($this->exactly(2))
            ->method('getContent')
            ->willReturnOnConsecutiveCalls(
                'Teapot',
                'Internal Error!'
            );

        $response->expects($this->exactly(2))
            ->method('withView')
            ->withConsecutive(
                ['errors/default', ['status' => 418, 'content' => 'Teapot'], 418],
                ['errors/505', ['status' => 505, 'content' => 'Internal Error!'], 505]
            )
            ->willReturn($response);

        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     */
    public function testProcessException()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ReturnResponseMiddlewareHandler|MockObject $returnResponseHandler */
        $returnResponseHandler = $this->getMockBuilder(ReturnResponseMiddlewareHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $psrResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(300);
        $psrResponse->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([]);

        $returnResponseHandler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function () {
                throw new HttpException(300, 'Some response', ['lor' => 'em']);
            });

        /** @var ErrorHandler|MockObject $errorHandler */
        $errorHandler = $this->getMockBuilder(ErrorHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['createResponse'])
            ->getMock();

        $errorHandler->expects($this->once())
            ->method('createResponse')
            ->with('Some response', 300, ['lor' => 'em'])
            ->willReturn($psrResponse);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($psrResponse, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     */
    public function testProcessContentTypeSniffer()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        $response = new Response('<!DOCTYPE html><html><body><h1>Hi!</h1></body></html>', 500);
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);

        /** @var ErrorHandler|MockObject $errorHandler */
        $errorHandler = new ErrorHandler($twigLoader);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return);
    }
}
