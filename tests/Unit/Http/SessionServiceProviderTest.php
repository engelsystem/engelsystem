<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\SessionHandlers\DatabaseHandler;
use Engelsystem\Http\SessionServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

#[CoversMethod(SessionServiceProvider::class, 'getSessionStorage')]
#[CoversMethod(SessionServiceProvider::class, 'register')]
#[CoversMethod(SessionServiceProvider::class, 'isCli')]
#[AllowMockObjectsWithoutExpectations]
class SessionServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = $this->getAppMock(['make', 'instance', 'bind', 'get']);

        $sessionStorage = $this->getStubBuilder(StorageInterface::class)->getStub();
        $sessionStorage2 = $this->getStubBuilder(StorageInterface::class)->getStub();
        $databaseHandler = $this->getStubBuilder(DatabaseHandler::class)
            ->disableOriginalConstructor()
            ->getStub();

        $session = $this->getSessionMock();
        $request = $this->getRequestMock();
        $request->server->set('HTTPS', 'on');

        $serviceProvider = $this->getMockBuilder(SessionServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['isCli'])
            ->getMock();

        $config = new Config([
            'session' => ['driver' => 'native', 'name' => 'session', 'lifetime' => 2],
        ]);

        $serviceProvider->expects($this->exactly(3))
            ->method('isCli')
            ->willReturnOnConsecutiveCalls(true, false, false);

        $matcher = $this->exactly(7);
        $app->expects($matcher)
            ->method('make')
            ->willReturnCallback(function (...$parameters) use (
                $sessionStorage2,
                $session,
                $sessionStorage,
                $matcher,
                $databaseHandler
            ) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(MockArraySessionStorage::class, $parameters[0]);
                    return $sessionStorage;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Session::class, $parameters[0]);
                    return $session;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(NativeSessionStorage::class, $parameters[0]);
                    $this->assertEquals([
                    // 2 days
                    'options' => [
                        'cookie_secure' => true,
                        'cookie_httponly' => true,
                        'name' => 'session',
                        'cookie_lifetime' => 172800,
                    ],
                    'handler' => null,
                    ], $parameters[1]);
                    return $sessionStorage2;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(Session::class, $parameters[0]);
                    return $session;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(DatabaseHandler::class, $parameters[0]);
                    return $databaseHandler;
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame(NativeSessionStorage::class, $parameters[0]);
                    $this->assertEquals([
                    // 5 days
                    'options' => [
                        'cookie_secure' => true,
                        'cookie_httponly' => true,
                        'name' => 'foobar',
                        'cookie_lifetime' => 432000,
                    ],
                    'handler' => $databaseHandler,
                    ], $parameters[1]);
                    return $sessionStorage2;
                }
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame(Session::class, $parameters[0]);
                    return $session;
                }
            });
        $matcher = $this->atLeastOnce();
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use ($matcher, $sessionStorage, $session): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('session.storage', $parameters[0]);
                    $this->assertSame($sessionStorage, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Session::class, $parameters[0]);
                    $this->assertSame($session, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('session', $parameters[0]);
                    $this->assertSame($session, $parameters[1]);
                }
            });

        $app->method('get')
            ->willReturnMap([
                ['request', $request],
                ['config', $config],
            ]);

        $matcher = $this->atLeastOnce();
        $app->expects($matcher)
            ->method('bind')
            ->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(StorageInterface::class, $parameters[0]);
                    $this->assertSame('session.storage', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(SessionInterface::class, $parameters[0]);
                    $this->assertSame(Session::class, $parameters[1]);
                }
            });

        $this->setExpects($request, 'setSession', [$session], null, $this->atLeastOnce());
        $this->setExpects($session, 'has', ['_token'], false, $this->atLeastOnce());
        $this->setExpects($session, 'set', ['_token'], null, $this->atLeastOnce());
        $this->setExpects($session, 'start', null, null, $this->atLeastOnce());

        $serviceProvider->register();
        $serviceProvider->register(); // native handler
        $config->set('session', ['driver' => 'pdo', 'name' => 'foobar', 'lifetime' => 5]);
        $serviceProvider->register(); // pdo handler
    }

    public function testIsCli(): void
    {
        $app = $this->getAppMock(['make', 'instance', 'bind', 'get']);

        $sessionStorage = $this->getStubBuilder(StorageInterface::class)->getStub();

        $session = $this->getSessionMock();
        $request = $this->getRequestMock();

        $app->method('make')
            ->willReturnMap([
                [MockArraySessionStorage::class, [], $sessionStorage],
                [Session::class, [], $session],
            ]);
        $matcher = $this->exactly(3);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use ($matcher, $sessionStorage, $session): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('session.storage', $parameters[0]);
                    $this->assertSame($sessionStorage, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Session::class, $parameters[0]);
                    $this->assertSame($session, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('session', $parameters[0]);
                    $this->assertSame($session, $parameters[1]);
                }
            });
        $matcher = $this->atLeastOnce();
        $app->expects($matcher)
            ->method('bind')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(StorageInterface::class, $parameters[0]);
                    $this->assertSame('session.storage', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(SessionInterface::class, $parameters[0]);
                    $this->assertSame(Session::class, $parameters[1]);
                }
            });

        $this->setExpects($app, 'get', ['request'], $request);
        $this->setExpects($request, 'setSession', [$session]);
        $this->setExpects($session, 'has', ['_token'], true);
        $this->setExpects($session, 'start');

        $serviceProvider = new SessionServiceProvider($app);
        $serviceProvider->register();
    }

    private function getSessionMock(): Session&MockObject
    {
        $sessionStorage = $this->getMockBuilder(StorageInterface::class)->getMock();
        return $this->getMockBuilder(Session::class)
            ->setConstructorArgs([$sessionStorage])
            ->onlyMethods(['start', 'has', 'set'])
            ->getMock();
    }

    private function getRequestMock(): Request&MockObject
    {
        return $this->getMockBuilder(Request::class)
            ->onlyMethods(['setSession'])
            ->getMock();
    }
}
