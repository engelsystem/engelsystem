<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Middleware\TrimInput;
use Engelsystem\Test\Unit\TestCase;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(TrimInput::class)]
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
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    /**
     * @return Generator<string, array>
     */
    public static function provideTrimTestData(): Generator
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

    #[DataProvider('provideTrimTestData')]
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
     */
    public function testTrimPostNull(): void
    {
        $request = $this->getStubBuilder(ServerRequestInterface::class)->getStub();
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
