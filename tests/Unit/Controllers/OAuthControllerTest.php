<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\AuthController;
use Engelsystem\Controllers\OAuthController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session as Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class OAuthControllerTest extends TestCase
{
    use HasDatabase;

    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var AuthController|MockObject */
    protected $authController;

    /** @var User */
    protected $authenticatedUser;

    /** @var User */
    protected $otherAuthenticatedUser;

    /** @var User */
    protected $otherUser;

    /** @var Config */
    protected $config;

    /** @var TestLogger */
    protected $log;

    /** @var OAuth */
    protected $oauth;

    /** @var Redirector|MockObject $redirect */
    protected $redirect;

    /** @var Session */
    protected $session;

    /** @var UrlGenerator|MockObject */
    protected $url;

    /** @var string[][] */
    protected $oauthConfig = [
        'testprovider' => [
            'client_id'     => 'testsystem',
            'client_secret' => 'foo-bar-baz',
            'url_auth'      => 'http://localhost/auth',
            'url_token'     => 'http://localhost/token',
            'url_info'      => 'http://localhost/info',
            'id'            => 'uid',
            'username'      => 'user',
            'email'         => 'email',
            'first_name'    => 'given-name',
            'last_name'     => 'last-name',
            'url'           => 'http://localhost/',
        ],
    ];

    /**
     * @covers \Engelsystem\Controllers\OAuthController::__construct
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::handleArrive
     */
    public function testIndexArrive()
    {
        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $this->session->set('oauth2_state', 'some-internal-state');
        $this->session->set('oauth2_connect_provider', 'testprovider');

        $accessToken = $this->createMock(AccessToken::class);
        $this->setExpects($accessToken, 'getToken', null, 'test-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getRefreshToken', null, 'test-refresh-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getExpires', null, 4242424242, $this->atLeastOnce());

        /** @var ResourceOwnerInterface|MockObject $resourceOwner */
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->setExpects($resourceOwner, 'toArray', null, [], $this->atLeastOnce());
        $resourceOwner->expects($this->exactly(7))
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
                'other-provider-user-identifier',
                'other-provider-user-identifier',
                'other-provider-user-identifier',
                'other-provider-user-identifier',
                'provider-user-identifier',
                'provider-user-identifier',
                'provider-user-identifier'
            );

        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $this->setExpects(
            $provider,
            'getAccessToken',
            ['authorization_code', ['code' => 'lorem-ipsum-code']],
            $accessToken,
            $this->atLeastOnce()
        );
        $this->setExpects($provider, 'getResourceOwner', [$accessToken], $resourceOwner, $this->atLeastOnce());

        $this->authController->expects($this->atLeastOnce())
            ->method('loginUser')
            ->willReturnCallback(function (User $user) {
                $this->assertTrue(in_array(
                    $user->id,
                    [$this->authenticatedUser->id, $this->otherUser->id]
                ));

                return new Response();
            });

        $this->auth->expects($this->exactly(4))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                $this->otherUser,
                null,
                null,
                null
            );

        $controller = $this->getMock(['getProvider', 'addNotification']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider, $this->atLeastOnce());
        $this->setExpects($controller, 'addNotification', ['oauth.connected']);

        // Connect to provider
        $controller->index($request);
        $this->assertTrue($this->log->hasInfoThatContains('Connected OAuth'));
        $this->assertCount(1, $this->otherUser->oauth);

        // Login using provider
        $controller->index($request);
        $this->assertFalse($this->session->has('oauth2_connect_provider'));
        $this->assertFalse((bool)$this->otherUser->state->arrived);

        // Tokens updated
        $oauth = $this->otherUser->oauth[0];
        $this->assertEquals('test-token', $oauth->access_token);
        $this->assertEquals('test-refresh-token', $oauth->refresh_token);
        $this->assertEquals(4242424242, $oauth->expires_at->unix());

        // Mark as arrived
        $oauthConfig = $this->config->get('oauth');
        $oauthConfig['testprovider']['mark_arrived'] = true;
        $this->config->set('oauth', $oauthConfig);
        $controller->index($request);

        $this->assertTrue((bool)User::find(1)->state->arrived);
        $this->assertTrue($this->log->hasInfoThatContains('as arrived'));
        $this->log->reset();

        // Don't set arrived if already done
        $controller->index($request);
        $this->assertTrue((bool)User::find(1)->state->arrived);
        $this->assertFalse($this->log->hasInfoThatContains('as arrived'));
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::getProvider
     */
    public function testIndexRedirectToProvider()
    {
        $this->redirect->expects($this->once())
            ->method('to')
            ->willReturnCallback(function ($url) {
                $this->assertStringStartsWith('http://localhost/auth', $url);
                $this->assertStringContainsString('testsystem', $url);
                $this->assertStringContainsString('code', $url);
                return new Response();
            });

        $this->setExpects($this->url, 'to', ['oauth/testprovider'], 'http://localhost/oauth/testprovider');

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider');

        $controller = $this->getMock();

        $controller->index($request);

        $this->assertNotEmpty($this->session->get('oauth2_state'));
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     */
    public function testIndexInvalidState()
    {
        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);

        $this->session->set('oauth2_state', 'some-internal-state');

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-wrong-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider);

        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->assertFalse($this->session->has('oauth2_state'));
        $this->log->hasWarningThatContains('Invalid');
        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.invalid-state', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     */
    public function testIndexProviderError()
    {
        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'lorem-ipsum-code'])
            ->willThrowException(new IdentityProviderException(
                'Oops',
                42,
                ['error' => 'some_error', 'error_description' => 'Some kind of error']
            ));

        $this->session->set('oauth2_state', 'some-internal-state');

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider);

        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->log->hasErrorThatContains('Some kind of error');
        $this->log->hasErrorThatContains('some_error');
        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.provider-error', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     */
    public function testIndexAlreadyConnectedToAUser()
    {
        $accessToken = $this->createMock(AccessToken::class);

        /** @var ResourceOwnerInterface|MockObject $resourceOwner */
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->setExpects($resourceOwner, 'getId', null, 'provider-user-identifier', $this->atLeastOnce());

        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $this->setExpects(
            $provider,
            'getAccessToken',
            ['authorization_code', ['code' => 'lorem-ipsum-code']],
            $accessToken,
            $this->atLeastOnce()
        );
        $this->setExpects($provider, 'getResourceOwner', [$accessToken], $resourceOwner);

        $this->session->set('oauth2_state', 'some-internal-state');

        $this->setExpects($this->auth, 'user', null, $this->otherAuthenticatedUser);

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider);

        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.already-connected', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::redirectRegisterOrThrowNotFound
     */
    public function testIndexRedirectRegister()
    {
        $accessToken = $this->createMock(AccessToken::class);
        $this->setExpects($accessToken, 'getToken', null, 'test-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getRefreshToken', null, 'test-refresh-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getExpires', null, 4242424242, $this->atLeastOnce());

        /** @var ResourceOwnerInterface|MockObject $resourceOwner */
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->setExpects(
            $resourceOwner,
            'getId',
            null,
            'ProVIdeR-User-IdenTifIer', // Case sensitive variation of existing entry
            $this->atLeastOnce()
        );
        $this->setExpects(
            $resourceOwner,
            'toArray',
            null,
            [
                'uid'        => 'ProVIdeR-User-IdenTifIer',
                'user'       => 'username',
                'email'      => 'foo.bar@localhost',
                'given-name' => 'Foo',
                'last-name'  => 'Bar',
            ],
            $this->atLeastOnce()
        );

        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $this->setExpects(
            $provider,
            'getAccessToken',
            ['authorization_code', ['code' => 'lorem-ipsum-code']],
            $accessToken,
            $this->atLeastOnce()
        );
        $this->setExpects($provider, 'getResourceOwner', [$accessToken], $resourceOwner, $this->atLeastOnce());

        $this->session->set('oauth2_state', 'some-internal-state');

        $this->setExpects($this->auth, 'user', null, null, $this->atLeastOnce());

        $this->setExpects($this->redirect, 'to', ['/register']);

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider, $this->atLeastOnce());

        $this->config->set('registration_enabled', true);
        $controller->index($request);
        $this->assertEquals('testprovider', $this->session->get('oauth2_connect_provider'));
        $this->assertEquals('ProVIdeR-User-IdenTifIer', $this->session->get('oauth2_user_id'));
        $this->assertEquals('test-token', $this->session->get('oauth2_access_token'));
        $this->assertEquals('test-refresh-token', $this->session->get('oauth2_refresh_token'));
        $this->assertEquals(4242424242, $this->session->get('oauth2_expires_at')->unix());
        $this->assertEquals(
            [
                'name' => 'username',
                'email' => 'foo.bar@localhost',
                'first_name' => 'Foo',
                'last_name' => 'Bar',
            ],
            $this->session->get('form_data')
        );

        $this->config->set('registration_enabled', false);
        $this->expectException(HttpNotFound::class);
        $controller->index($request);
    }


    /**
     * @covers \Engelsystem\Controllers\OAuthController::connect
     * @covers \Engelsystem\Controllers\OAuthController::requireProvider
     * @covers \Engelsystem\Controllers\OAuthController::isValidProvider
     */
    public function testConnect()
    {
        $controller = $this->getMock(['index']);
        $this->setExpects($controller, 'index', null, new Response());

        $request = (new Request())
            ->withAttribute('provider', 'testprovider');

        $controller->connect($request);

        $this->assertEquals('testprovider', $this->session->get('oauth2_connect_provider'));

        // Provider not found
        $request = $request->withAttribute('provider', 'notExistingProvider');
        $this->expectException(HttpNotFound::class);

        $controller->connect($request);
    }


    /**
     * @covers \Engelsystem\Controllers\OAuthController::disconnect
     */
    public function testDisconnect()
    {
        $controller = $this->getMock(['addNotification']);
        $this->setExpects($controller, 'addNotification', ['oauth.disconnected']);

        $request = (new Request())
            ->withAttribute('provider', 'testprovider');

        $this->setExpects($this->auth, 'user', null, $this->authenticatedUser);
        $this->setExpects($this->redirect, 'back', null, new Response());

        $controller->disconnect($request);
        $this->assertCount(1, OAuth::all());
        $this->log->hasInfoThatContains('Disconnected');
    }

    /**
     * @param array $mockMethods
     *
     * @return OAuthController|MockObject
     */
    protected function getMock(array $mockMethods = []): OAuthController
    {
        /** @var OAuthController|MockObject $controller */
        $controller = $this->getMockBuilder(OAuthController::class)
            ->setConstructorArgs([
                $this->auth,
                $this->authController,
                $this->config,
                $this->log,
                $this->oauth,
                $this->redirect,
                $this->session,
                $this->url
            ])
            ->onlyMethods($mockMethods)
            ->getMock();

        return $controller;
    }

    /**
     * Setup the DB
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();

        $this->auth = $this->createMock(Authenticator::class);
        $this->authController = $this->createMock(AuthController::class);
        $this->config = new Config(['oauth' => $this->oauthConfig]);
        $this->log = new TestLogger();
        $this->oauth = new OAuth();
        $this->redirect = $this->createMock(Redirector::class);
        $this->session = new Session(new MockArraySessionStorage());
        $this->url = $this->createMock(UrlGenerator::class);

        $this->app->instance('session', $this->session);

        $this->authenticatedUser = User::factory()->create();
        (new OAuth(['provider' => 'testprovider', 'identifier' => 'provider-user-identifier']))
            ->user()
            ->associate($this->authenticatedUser)
            ->save();

        $this->otherUser = User::factory()->create();

        $this->otherAuthenticatedUser = User::factory()->create();
        (new OAuth(['provider' => 'testprovider', 'identifier' => 'provider-baz-identifier']))
            ->user()
            ->associate($this->otherAuthenticatedUser)
            ->save();
    }
}
