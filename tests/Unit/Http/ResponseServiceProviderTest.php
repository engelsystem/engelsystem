<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use Engelsystem\Http\ResponseServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[CoversMethod(ResponseServiceProvider::class, 'register')]
class ResponseServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $response = $this->getStubBuilder(Response::class)
            ->getStub();

        $app = $this->getAppMock();

        $this->setExpects($app, 'make', [Response::class], $response);
        $matcher = $this->exactly(3);
        $app->expects($matcher)
            ->method('instance')->willReturnCallback(function (...$parameters) use ($matcher, $response): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(Response::class, $parameters[0]);
                    $this->assertSame($response, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(SymfonyResponse::class, $parameters[0]);
                    $this->assertSame($response, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('response', $parameters[0]);
                    $this->assertSame($response, $parameters[1]);
                }
            });

        $serviceProvider = new ResponseServiceProvider($app);
        $serviceProvider->register();
    }
}
