<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Middleware\TrimInput;
use Engelsystem\Test\Unit\TestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrimInputTest extends TestCase
{
    private TrimInput $subject;

    /**
     * @var MockObject<RequestHandlerInterface>
     */
    private MockObject $handler;

    public function setUp(): void
    {
        $this->subject = new TrimInput();
        $this->handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
    }

    /**
     * @return Generator<string, array>
     */
    public function provideTrimTestData(): Generator
    {
        yield 'GET request' => ['GET', [], []];

        foreach (['POST', 'PUT'] as $method) {
            yield $method . ' request with empty data' => [$method, [], []];
            yield $method . ' request with mixed data' => [
                $method,
                [
                    'fieldA' => 23,
                    'fieldB' => ' bla ',
                    'password' => ' pass1 ',
                    'password2' => ' pass2 ',
                    'new_password' => ' new_password ',
                    'new_password2' => ' new_password2 ',
                    'new_pw' => ' new_pw ',
                    'new_pw2' => ' new_pw2 ',
                    'password_confirmation' => ' password_confirmation ',
                    'fieldC' => ['sub' => ' bla2 '],
                    'fieldD' => null,
                ],
                [
                    'fieldA' => 23,
                    'fieldB' => 'bla',
                    // password fields should keep their surrounding spaces
                    'password' => ' pass1 ',
                    'password2' => ' pass2 ',
                    'new_password' => ' new_password ',
                    'new_password2' => ' new_password2 ',
                    'new_pw' => ' new_pw ',
                    'new_pw2' => ' new_pw2 ',
                    'password_confirmation' => ' password_confirmation ',
                    'fieldC' => ['sub' => 'bla2'],
                    'fieldD' => null,
                ],
            ];
        }
    }

    /**
     * @covers \Engelsystem\Middleware\TrimInput
     * @dataProvider provideTrimTestData
     */
    public function testTrim(string $method, mixed $body, mixed $expectedBody): void
    {
        $request = (new Request())->withMethod($method)->withParsedBody($body);
        $this->handler->expects(self::once())->method('handle')->with(
            self::callback(function (ServerRequestInterface $request) use ($expectedBody): bool {
                self::assertSame($expectedBody, $request->getParsedBody());
                return true;
            })
        );
        $this->subject->process($request, $this->handler);
    }

    /**
     * Special test case to cover null value parsed body.
     *
     * @covers \Engelsystem\Middleware\TrimInput
     */
    public function testTrimPostNull(): void
    {
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn(null);
        $this->handler->expects(self::once())->method('handle')->with(
            self::callback(function (ServerRequestInterface $request): bool {
                self::assertNull($request->getParsedBody());
                return true;
            })
        );
        $this->subject->process($request, $this->handler);
    }
}
