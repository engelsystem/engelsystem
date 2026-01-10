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
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface as StorageInterface;

#[CoversFunction('app')]
#[CoversFunction('auth')]
#[CoversFunction('base_path')]
#[CoversFunction('config')]
#[CoversFunction('env_secret')]
#[CoversFunction('back')]
#[CoversFunction('cache')]
#[CoversFunction('config_path')]
#[CoversFunction('event')]
#[CoversFunction('redirect')]
#[CoversFunction('request')]
#[CoversFunction('response')]
#[CoversFunction('session')]
#[CoversFunction('view')]
#[CoversFunction('__')]
#[CoversFunction('trans')]
#[CoversFunction('_e')]
#[CoversFunction('url')]
class HelpersTest extends TestCase
{
    public function testApp(): void
    {
        $class = new class
        {
        };

        $appMock = $this->getAppMock('some.name', $class);

        $this->assertEquals($appMock, app());
        $this->assertEquals($class, app('some.name'));
    }

    public function testAuth(): void
    {
        $app = $this->createMock(Container::class);
        Application::setInstance($app);
        $auth = $this->getStubBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getStub();

        $app->expects($this->once())
            ->method('get')
            ->with('authenticator')
            ->willReturn($auth);

        $this->assertEquals($auth, auth());
    }

    public function testBasePath(): void
    {
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

    public function testBack(): void
    {
        $response = new Response();
        $redirect = $this->createMock(Redirector::class);
        $matcher = $this->exactly(2);
        $redirect->expects($matcher)
            ->method('back')->willReturnCallback(function (...$parameters) use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(302, $parameters[0]);
                    $this->assertSame([], $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(303, $parameters[0]);
                    $this->assertSame(['test' => 'ing'], $parameters[1]);
                }
                return $response;
            });

        $app = new Application();
        $app->instance('redirect', $redirect);

        $return = back();
        $this->assertEquals($response, $return);

        $return = back(303, ['test' => 'ing']);
        $this->assertEquals($response, $return);
    }

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

    public function testConfigPath(): void
    {
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

    public function testEvent(): void
    {
        $app = $this->createMock(Container::class);
        Application::setInstance($app);

        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->setExpects($dispatcher, 'dispatch', ['testevent', ['some' => 'thing']], $dispatcher);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->with('events.dispatcher')
            ->willReturn($dispatcher);

        $this->assertEquals($dispatcher, event());
        $this->assertEquals($dispatcher, event('testevent', ['some' => 'thing']));
    }

    public function testRedirect(): void
    {
        $response = new Response();
        $redirect = $this->createMock(Redirector::class);
        $matcher = $this->exactly(2);
        $redirect->expects($matcher)
            ->method('to')->willReturnCallback(function (...$parameters) use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('/lorem', $parameters[0]);
                    $this->assertSame(302, $parameters[1]);
                    $this->assertSame([], $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('/ipsum', $parameters[0]);
                    $this->assertSame(303, $parameters[1]);
                    $this->assertSame(['test' => 'er'], $parameters[2]);
                }
                return $response;
            });

        $app = new Application();
        $app->instance('redirect', $redirect);

        $return = redirect('/lorem');
        $this->assertEquals($response, $return);

        $return = redirect('/ipsum', 303, ['test' => 'er']);
        $this->assertEquals($response, $return);
    }

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

    public function testResponse(): void
    {
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

        $matcher = $this->exactly(2);
        $response->expects($matcher)
            ->method('withAddedHeader')->willReturnCallback(function (...$parameters) use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('lor', $parameters[0]);
                    $this->assertSame('em', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('foo', $parameters[0]);
                    $this->assertSame('bar', $parameters[1]);
                }
                return $response;
            });

        $this->assertEquals($response, response('Lorem Ipsum?', 501, ['lor' => 'em', 'foo' => 'bar']));
    }

    public function testSession(): void
    {
        $sessionStorage = $this->getStubBuilder(StorageInterface::class)->getStub();
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

    public function testTrans(): void
    {
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

    public function testTranslatePlural(): void
    {
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

    public function testUrl(): void
    {
        $urlGeneratorMock = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();

        $this->getAppMock('http.urlGenerator', $urlGeneratorMock);
        $this->assertEquals($urlGeneratorMock, url());

        $urlGeneratorMock->expects($this->once())
            ->method('to')
            ->with('foo/bar', ['param' => 'value'])
            ->willReturn('http://lorem.ipsum/foo/bar?param=value');

        $this->assertEquals('http://lorem.ipsum/foo/bar?param=value', url('foo/bar', ['param' => 'value']));
    }

    protected function getAppMock(string $alias, object $object): Container&MockObject
    {
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
