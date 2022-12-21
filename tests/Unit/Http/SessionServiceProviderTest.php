<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\SessionHandlers\DatabaseHandler;
use Engelsystem\Http\SessionServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

class SessionServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\SessionServiceProvider::getSessionStorage()
     * @covers \Engelsystem\Http\SessionServiceProvider::register()
     */
    public function testRegister(): void
    {
        $app = $this->getApp(['make', 'instance', 'bind', 'get']);

        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);
        $sessionStorage2 = $this->getMockForAbstractClass(StorageInterface::class);
        $databaseHandler = $this->getMockBuilder(DatabaseHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $session = $this->getSessionMock();
        $request = $this->getRequestMock();

        /** @var SessionServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(SessionServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['isCli'])
            ->getMock();

        /** @var Config|MockObject $config */
        $config = new Config([
            'session' => ['driver' => 'native', 'name' => 'session', 'lifetime' => 2],
        ]);

        $serviceProvider->expects($this->exactly(3))
            ->method('isCli')
            ->willReturnOnConsecutiveCalls(true, false, false);

        $app->expects($this->exactly(7))
            ->method('make')
            ->withConsecutive(
                [MockArraySessionStorage::class],
                [Session::class],
                [
                    NativeSessionStorage::class,
                    [
                        // 2 days
                        'options' => ['cookie_httponly' => true, 'name' => 'session', 'cookie_lifetime' => 172800],
                        'handler' => null
                    ],
                ],
                [Session::class],
                [DatabaseHandler::class],
                [
                    NativeSessionStorage::class,
                    [
                        // 5 days
                        'options' => ['cookie_httponly' => true, 'name' => 'foobar', 'cookie_lifetime' => 432000],
                        'handler' => $databaseHandler
                    ],
                ],
                [Session::class]
            )
            ->willReturnOnConsecutiveCalls(
                $sessionStorage,
                $session,
                $sessionStorage2,
                $session,
                $databaseHandler,
                $sessionStorage2,
                $session
            );
        $app->expects($this->atLeastOnce())
            ->method('instance')
            ->withConsecutive(
                ['session.storage', $sessionStorage],
                [Session::class, $session],
                ['session', $session]
            );

        $app->expects($this->exactly(5))
            ->method('get')
            ->withConsecutive(
                ['request'],
                ['config'],
                ['request'],
                ['config'],
                ['request']
            )
            ->willReturnOnConsecutiveCalls(
                $request,
                $config,
                $request,
                $config,
                $request
            );

        $app->expects($this->atLeastOnce())
            ->method('bind')
            ->withConsecutive(
                [StorageInterface::class, 'session.storage'],
                [SessionInterface::class, Session::class]
            );

        $this->setExpects($request, 'setSession', [$session], null, $this->atLeastOnce());
        $this->setExpects($session, 'has', ['_token'], false, $this->atLeastOnce());
        $this->setExpects($session, 'set', ['_token'], null, $this->atLeastOnce());
        $this->setExpects($session, 'start', null, null, $this->atLeastOnce());

        $serviceProvider->register();
        $serviceProvider->register(); // native handler
        $config->set('session', ['driver' => 'pdo', 'name' => 'foobar', 'lifetime' => 5]);
        $serviceProvider->register(); // pdo handler
    }

    /**
     * @covers \Engelsystem\Http\SessionServiceProvider::isCli()
     */
    public function testIsCli(): void
    {
        $app = $this->getApp(['make', 'instance', 'bind', 'get']);

        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);

        $session = $this->getSessionMock();
        $request = $this->getRequestMock();

        $app->expects($this->exactly(2))
            ->method('make')
            ->withConsecutive(
                [MockArraySessionStorage::class],
                [Session::class]
            )
            ->willReturnOnConsecutiveCalls(
                $sessionStorage,
                $session
            );
        $app->expects($this->exactly(3))
            ->method('instance')
            ->withConsecutive(
                ['session.storage', $sessionStorage],
                [Session::class, $session],
                ['session', $session]
            );
        $app->expects($this->atLeastOnce())
            ->method('bind')
            ->withConsecutive(
                [StorageInterface::class, 'session.storage'],
                [SessionInterface::class, Session::class]
            );

        $this->setExpects($app, 'get', ['request'], $request);
        $this->setExpects($request, 'setSession', [$session]);
        $this->setExpects($session, 'has', ['_token'], true);
        $this->setExpects($session, 'start');

        $serviceProvider = new SessionServiceProvider($app);
        $serviceProvider->register();
    }

    private function getSessionMock(): MockObject
    {
        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);
        return $this->getMockBuilder(Session::class)
            ->setConstructorArgs([$sessionStorage])
            ->onlyMethods(['start', 'has', 'set'])
            ->getMock();
    }

    private function getRequestMock(): MockObject
    {
        return $this->getMockBuilder(Request::class)
            ->onlyMethods(['setSession'])
            ->getMock();
    }
}
