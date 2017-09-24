<?php

namespace Engelsystem\Test\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Http\Request;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Routing\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;

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
        $sessionMock = $this->getMockBuilder(Session::class)
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
     * @return Application|\PHPUnit_Framework_MockObject_MockObject
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
