<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Http\Request;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Routing\UrlGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

class HelpersTest extends TestCase
{
    /**
     * @covers \app
     */
    public function testApp()
    {
        $class = new class
        {
        };

        $appMock = $this->getAppMock('some.name', $class);

        $this->assertEquals($appMock, app());
        $this->assertEquals($class, app('some.name'));
    }

    /**
     * @covers \base_path()
     */
    public function testBasePath()
    {
        /** @var MockObject|Application $app */
        $app = $this->getMockBuilder(Container::class)
            ->getMock();
        Application::setInstance($app);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->with('path')
            ->willReturn('/foo/bar');

        $this->assertEquals('/foo/bar', base_path());
        $this->assertEquals('/foo/bar/bla-foo.conf', base_path('bla-foo.conf'));
    }

    /**
     * @covers \config
     */
    public function testConfig()
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->getMock();

        $this->getAppMock('config', $configMock);
        $this->assertEquals($configMock, config());

        $configMock->expects($this->once())
            ->method('set')
            ->with(['foo' => 'bar']);

        $this->assertTrue(config(['foo' => 'bar']));

        $configMock->expects($this->once())
            ->method('get')
            ->with('mail')
            ->willReturn(['user' => 'FooBar']);

        $this->assertEquals(['user' => 'FooBar'], config('mail'));
    }

    /**
     * @covers \config_path()
     */
    public function testConfigPath()
    {
        /** @var MockObject|Application $app */
        $app = $this->getMockBuilder(Container::class)
            ->getMock();
        Application::setInstance($app);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->with('path.config')
            ->willReturn('/foo/conf');

        $this->assertEquals('/foo/conf', config_path());
        $this->assertEquals('/foo/conf/bar.php', config_path('bar.php'));
    }

    /**
     * @covers \env
     */
    public function testEnv()
    {
        putenv('envTestVar=someContent');

        $env = env('envTestVar');
        $this->assertEquals('someContent', $env);

        $env = env('someRandomEnvVarThatShouldNeverExist', 'someDefaultValue');
        $this->assertEquals('someDefaultValue', $env);
    }

    /**
     * @covers \request
     */
    public function testRequest()
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->getMock();

        $this->getAppMock('request', $requestMock);
        $this->assertEquals($requestMock, request());

        $requestMock->expects($this->once())
            ->method('input')
            ->with('requestKey')
            ->willReturn('requestValue');

        $this->assertEquals('requestValue', request('requestKey'));
    }

    /**
     * @covers \session
     */
    public function testSession()
    {
        $sessionStorage = $this->getMockForAbstractClass(StorageInterface::class);
        $sessionMock = $this->getMockBuilder(Session::class)
            ->setConstructorArgs([$sessionStorage])
            ->getMock();

        $this->getAppMock('session', $sessionMock);
        $this->assertEquals($sessionMock, session());

        $sessionMock->expects($this->once())
            ->method('get')
            ->with('someKey')
            ->willReturn('someValue');

        $this->assertEquals('someValue', session('someKey'));
    }

    /**
     * @covers \view
     */
    public function testView()
    {
        $rendererMock = $this->getMockBuilder(Renderer::class)
            ->getMock();

        $this->getAppMock('renderer', $rendererMock);
        $this->assertEquals($rendererMock, view());

        $rendererMock->expects($this->once())
            ->method('render')
            ->with('template.name', ['template' => 'data'])
            ->willReturn('rendered template');

        $this->assertEquals('rendered template', view('template.name', ['template' => 'data']));
    }

    /**
     * @covers \url
     */
    public function testUrl()
    {
        $urlGeneratorMock = $this->getMockBuilder(UrlGenerator::class)
            ->getMock();

        $this->getAppMock('routing.urlGenerator', $urlGeneratorMock);
        $this->assertEquals($urlGeneratorMock, url());

        $urlGeneratorMock->expects($this->once())
            ->method('to')
            ->with('foo/bar', ['param' => 'value'])
            ->willReturn('http://lorem.ipsum/foo/bar?param=value');

        $this->assertEquals('http://lorem.ipsum/foo/bar?param=value', url('foo/bar', ['param' => 'value']));
    }

    /**
     * @param string $alias
     * @param object $object
     * @return Application|MockObject
     */
    protected function getAppMock($alias, $object)
    {
        $appMock = $this->getMockBuilder(Container::class)
            ->getMock();

        $appMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($alias)
            ->willReturn($object);

        /** @var $appMock Application */
        Application::setInstance($appMock);

        return $appMock;
    }
}
