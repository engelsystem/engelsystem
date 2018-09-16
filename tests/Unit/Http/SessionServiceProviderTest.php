<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\SessionHandlers\DatabaseHandler;
use Engelsystem\Http\SessionServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

class SessionServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\SessionServiceProvider::register()
     * @covers \Engelsystem\Http\SessionServiceProvider::getSessionStorage()
     */
    public function testRegister()
    {
        $app = $this->getApp(['make', 'instance', 'bind', 'get']);

        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);
        $sessionStorage2 = $this->getMockForAbstractClass(StorageInterface::class);
        $databaseHandler = $this->getMockBuilder(DatabaseHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $session = $this->getSessionMock();
        $request = $this->getRequestMock();

        /** @var MockObject|SessionServiceProvider $serviceProvider */
        $serviceProvider = $this->getMockBuilder(SessionServiceProvider::class)
            ->setConstructorArgs([$app])
            ->setMethods(['isCli'])
            ->getMock();

        /** @var Config|MockObject $config */
        $config = $this->createMock(Config::class);

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
                    ['options' => ['cookie_httponly' => true, 'name' => 'session'], 'handler' => null]
                ],
                [Session::class],
                [DatabaseHandler::class],
                [
                    NativeSessionStorage::class,
                    ['options' => ['cookie_httponly' => true, 'name' => 'foobar'], 'handler' => $databaseHandler]
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

        $config->expects($this->exactly(2))
            ->method('get')
            ->with('session')
            ->willReturnOnConsecutiveCalls(
                ['driver' => 'native', 'name' => 'session'],
                ['driver' => 'pdo', 'name' => 'foobar']
            );

        $this->setExpects($app, 'bind', [StorageInterface::class, 'session.storage'], null, $this->atLeastOnce());
        $this->setExpects($request, 'setSession', [$session], null, $this->atLeastOnce());
        $this->setExpects($session, 'start', null, null, $this->atLeastOnce());

        $serviceProvider->register();
        $serviceProvider->register();
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Http\SessionServiceProvider::isCli()
     */
    public function testIsCli()
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

        $this->setExpects($app, 'bind', [StorageInterface::class, 'session.storage']);
        $this->setExpects($app, 'get', ['request'], $request);
        $this->setExpects($request, 'setSession', [$session]);
        $this->setExpects($session, 'start');

        $serviceProvider = new SessionServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @return MockObject
     */
    private function getSessionMock()
    {
        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);
        return $this->getMockBuilder(Session::class)
            ->setConstructorArgs([$sessionStorage])
            ->setMethods(['start'])
            ->getMock();
    }

    /**
     * @return MockObject
     */
    private function getRequestMock()
    {
        return $this->getMockBuilder(Request::class)
            ->setMethods(['setSession'])
            ->getMock();
    }
}
