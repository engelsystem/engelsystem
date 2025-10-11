<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Cache;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Renderer\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

class HelpersTest extends TestCase
{
    /**
     * @covers \app
     */
    public function testApp(): void
    {
        $class = new class
        {
        };

        $appMock = $this->getAppMock('some.name', $class);

        $this->assertEquals($appMock, app());
        $this->assertEquals($class, app('some.name'));
    }

    /**
     * @covers \auth
     */
    public function testAuth(): void
    {
        /** @var Application|MockObject $app */
        $app = $this->createMock(Container::class);
        Application::setInstance($app);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app->expects($this->once())
            ->method('get')
            ->with('authenticator')
            ->willReturn($auth);

        $this->assertEquals($auth, auth());
    }

    /**
     * @covers \base_path
     */
    public function testBasePath(): void
    {
        /** @var Application|MockObject $app */
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
    public function testConfig(): void
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
     * @covers \env_secret
     */
    public function testEnvSecret(): void
    {
        $filename = __DIR__ . '/Assets/foo_secret';

        // Secret from env var when _FILE is emtpy
        putenv('FOO=');
        putenv('FOO_FILE=');
        $this->assertEquals('', env_secret('FOO'));

        putenv('FOO=baz');
        putenv('FOO_FILE=');
        $this->assertEquals('baz', env_secret('FOO'));

        // Load secret from file
        putenv('FOO=');
        putenv('FOO_FILE=' . $filename);
        $this->assertEquals('bar' . PHP_EOL, env_secret('FOO'));

        putenv('FOO=baz');
        putenv('FOO_FILE=' . $filename);
        $this->assertEquals('bar' . PHP_EOL, env_secret('FOO'));

        // Fallback to env/default when file does not exist / is not readable
        putenv('FOO=test');
        putenv('FOO_FILE=/not/existing/file');
        $this->assertEquals('test', env_secret('FOO'));

        putenv('BAR_FILE=/not/existing/file');
        $this->assertEquals('default-value', env_secret('BAR', 'default-value'));
    }

    /**
     * @covers \back
     */
    public function testBack(): void
    {
        $response = new Response();
        /** @var Redirector|MockObject $redirect */
        $redirect = $this->createMock(Redirector::class);
        $redirect->expects($this->exactly(2))
            ->method('back')
            ->withConsecutive([302, []], [303, ['test' => 'ing']])
            ->willReturn($response);

        $app = new Application();
        $app->instance('redirect', $redirect);

        $return = back();
        $this->assertEquals($response, $return);

        $return = back(303, ['test' => 'ing']);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \cache
     */
    public function testCache(): void
    {
        $cache = $this->createMock(Cache::class);
        $this->setExpects($cache, 'get', ['test', 'default', 42], 'default');

        $app = new Application();
        $app->instance('cache', $cache);

        $this->assertEquals($cache, cache());

        $return = cache('test', 'default', 42);
        $this->assertEquals('default', $return);
    }

    /**
     * @covers \config_path
     */
    public function testConfigPath(): void
    {
        /** @var Application|MockObject $app */
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
     * @covers \event
     */
    public function testEvent(): void
    {
        /** @var Application|MockObject $app */
        $app = $this->createMock(Container::class);
        Application::setInstance($app);

        /** @var EventDispatcher|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->setExpects($dispatcher, 'dispatch', ['testevent', ['some' => 'thing']], $dispatcher);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->with('events.dispatcher')
            ->willReturn($dispatcher);

        $this->assertEquals($dispatcher, event());
        $this->assertEquals($dispatcher, event('testevent', ['some' => 'thing']));
    }

    /**
     * @covers \redirect
     */
    public function testRedirect(): void
    {
        $response = new Response();
        /** @var Redirector|MockObject $redirect */
        $redirect = $this->createMock(Redirector::class);
        $redirect->expects($this->exactly(2))
            ->method('to')
            ->withConsecutive(['/lorem', 302, []], ['/ipsum', 303, ['test' => 'er']])
            ->willReturn($response);

        $app = new Application();
        $app->instance('redirect', $redirect);

        $return = redirect('/lorem');
        $this->assertEquals($response, $return);

        $return = redirect('/ipsum', 303, ['test' => 'er']);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \request
     */
    public function testRequest(): void
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
     * @covers \response
     */
    public function testResponse(): void
    {
        /** @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)->getMock();
        $this->getAppMock('psr7.response', $response);

        $response->expects($this->once())
            ->method('withContent')
            ->with('Lorem Ipsum?')
            ->willReturn($response);

        $response->expects($this->once())
            ->method('withStatus')
            ->with(501)
            ->willReturn($response);

        $response->expects($this->exactly(2))
            ->method('withAddedHeader')
            ->withConsecutive(['lor', 'em'], ['foo', 'bar'])
            ->willReturn($response);

        $this->assertEquals($response, response('Lorem Ipsum?', 501, ['lor' => 'em', 'foo' => 'bar']));
    }

    /**
     * @covers \session
     */
    public function testSession(): void
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
    public function testView(): void
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
     * @covers \__
     * @covers \trans
     */
    public function testTrans(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getAppMock('translator', $translator);

        $translator->expects($this->exactly(2))
            ->method('translate')
            ->with('Lorem %s Ipsum', ['foo'])
            ->willReturn('Lorem foo Ipsum');

        $this->assertEquals($translator, trans());
        $this->assertEquals('Lorem foo Ipsum', trans('Lorem %s Ipsum', ['foo']));
        $this->assertEquals('Lorem foo Ipsum', __('Lorem %s Ipsum', ['foo']));
    }

    /**
     * @covers \_e
     */
    public function testTranslatePlural(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getAppMock('translator', $translator);

        $translator->expects($this->once())
            ->method('translatePlural')
            ->with('One: %u', 'Multiple: %u', 4, [4])
            ->willReturn('Multiple: 4');

        $this->assertEquals('Multiple: 4', _e('One: %u', 'Multiple: %u', 4, [4]));
    }

    /**
     * @covers \url
     */
    public function testUrl(): void
    {
        $urlGeneratorMock = $this->getMockForAbstractClass(UrlGeneratorInterface::class);

        $this->getAppMock('http.urlGenerator', $urlGeneratorMock);
        $this->assertEquals($urlGeneratorMock, url());

        $urlGeneratorMock->expects($this->once())
            ->method('to')
            ->with('foo/bar', ['param' => 'value'])
            ->willReturn('http://lorem.ipsum/foo/bar?param=value');

        $this->assertEquals('http://lorem.ipsum/foo/bar?param=value', url('foo/bar', ['param' => 'value']));
    }

    protected function getAppMock(string $alias, object $object): Application|MockObject
    {
        /** @var Application|MockObject $appMock */
        $appMock = $this->getMockBuilder(Container::class)
            ->getMock();

        $appMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($alias)
            ->willReturn($object);

        Application::setInstance($appMock);

        return $appMock;
    }
}
