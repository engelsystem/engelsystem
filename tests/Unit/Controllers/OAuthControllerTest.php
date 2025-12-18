<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\AuthController;
use Engelsystem\Controllers\OAuthController;
use Engelsystem\Events\EventDispatcher;
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

    protected Authenticator|MockObject $auth;

    protected AuthController|MockObject $authController;

    protected User $authenticatedUser;

    protected User $otherAuthenticatedUser;

    protected User $otherUser;

    protected Config $config;

    protected TestLogger $log;

    protected OAuth $oauth;

    protected Redirector|MockObject $redirect;

    protected Session $session;

    protected UrlGenerator|MockObject $url;

    /** @var string[][] */
    protected array $oauthConfig = [
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
            'scope'         => ['foo', 'bar'],
        ],
    ];

    /**
     * @covers \Engelsystem\Controllers\OAuthController::__construct
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::handleArrive
     * @covers \Engelsystem\Controllers\OAuthController::getId
     */
    public function testIndexArrive(): void
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
        $resourceOwner->expects($this->exactly(5))
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
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

        /** @var EventDispatcher|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $dispatcher);
        $this->setExpects($dispatcher, 'dispatch', ['oauth2.login'], $dispatcher, 4);

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
        $this->assertFalse((bool) $this->otherUser->state->arrived);

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

        $this->assertTrue((bool) User::find(1)->state->arrived);
        $this->assertTrue($this->log->hasInfoThatContains('as arrived'));
        $this->log->reset();

        // Don't set arrived if already done
        $controller->index($request);
        $this->assertTrue((bool) User::find(1)->state->arrived);
        $this->assertFalse($this->log->hasInfoThatContains('as arrived'));
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::getProvider
     */
    public function testIndexRedirectToProvider(): void
    {
        $this->redirect->expects($this->once())
            ->method('to')
            ->willReturnCallback(function ($url) {
                $this->assertStringStartsWith('http://localhost/auth', $url);
                $this->assertStringContainsString('testsystem', $url);
                $this->assertStringContainsString('code', $url);
                $this->assertStringContainsString('scope=foo%20bar', $url);
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
    public function testIndexInvalidState(): void
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
        $this->assertTrue($this->log->hasWarningThatContains('Invalid'));
        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.invalid-state', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::handleOAuthError
     */
    public function testIndexProviderErrorOnResourceInfo(): void
    {
        /** @var AccessToken|MockObject $accessToken */
        $accessToken = $this->createMock(AccessToken::class);

        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $this->setExpects(
            $provider,
            'getAccessToken',
            ['authorization_code', ['code' => 'lorem-ipsum-code']],
            $accessToken
        );
        $provider->expects($this->once())
            ->method('getResourceOwner')
            ->with($accessToken)
            ->willReturnCallback(function (): void {
                throw new IdentityProviderException(
                    'Something\'s wrong!',
                    1337,
                    '500 Internal server error'
                );
            });

        $this->session->set('oauth2_state', 'some-internal-state');

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider, 1);

        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->assertTrue($this->log->hasErrorThatPasses(function ($rec): bool {
            return str_contains($rec['message'], 'identity provider error')
                && $rec['context']['error'] == 'Something\'s wrong!';
        }));
        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.provider-error', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::handleOAuthError
     */
    public function testIndexProviderErrorIdentityProvider(): void
    {
        /** @var GenericProvider|MockObject $provider */
        $provider = $this->createMock(GenericProvider::class);
        $provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'lorem-ipsum-code'])
            ->willReturnCallback(function (): void {
                throw new IdentityProviderException(
                    'Oops',
                    42,
                    ['error' => 'some_error', 'error_description' => 'Some kind of error']
                );
            });

        $this->session->set('oauth2_state', 'some-internal-state');

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['code' => 'lorem-ipsum-code', 'state' => 'some-internal-state']);

        $controller = $this->getMock(['getProvider']);
        $this->setExpects($controller, 'getProvider', ['testprovider'], $provider, 1);

        // Error while getting data
        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->assertTrue($this->log->hasErrorThatPasses(function ($rec): bool {
            return str_contains($rec['message'], 'identity provider error')
                && $rec['context']['error'] == 'Oops'
                && str_contains($rec['context']['description'], 'Some kind of error');
        }));
        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.provider-error', $exception->getMessage());
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     */
    public function testIndexAlreadyConnectedToAUser(): void
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
     * @covers       \Engelsystem\Controllers\OAuthController::index
     * @dataProvider oAuthErrorCodeProvider
     */
    public function testIndexOAuthErrorResponse(string $oauth_error_code): void
    {
        $controller = $this->getMock(['getProvider']);

        $request = new Request();
        $request = $request
            ->withAttribute('provider', 'testprovider')
            ->withQueryParams(['error' => $oauth_error_code]);

        $exception = null;
        try {
            $controller->index($request);
        } catch (HttpNotFound $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'Exception not thrown');
        $this->assertEquals('oauth.' . $oauth_error_code, $exception->getMessage());
    }

    public function oAuthErrorCodeProvider(): array
    {
        return [
            ['invalid_request'],
            ['unauthorized_client'],
            ['access_denied'],
            ['unsupported_response_type'],
            ['invalid_scope'],
            ['server_error'],
            ['temporarily_unavailable'],
        ];
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::redirectRegister
     */
    public function testIndexRedirectRegister(): void
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
            'ProVIdeR-User-IdenTifIer', // case-sensitive variation of existing entry
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
        $this->assertFalse($this->session->get('oauth2_enable_password'));
        $this->assertEquals(null, $this->session->get('oauth2_allow_registration'));
        $this->assertEquals('username', $this->session->get('form-data-username'));
        $this->assertEquals('foo.bar@localhost', $this->session->get('form-data-email'));
        $this->assertEquals('Foo', $this->session->get('form-data-firstname'));
        $this->assertEquals('Bar', $this->session->get('form-data-lastname'));

        $this->config->set('registration_enabled', false);
        $this->expectException(HttpNotFound::class);
        $controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::index
     * @covers \Engelsystem\Controllers\OAuthController::getId
     * @covers \Engelsystem\Controllers\OAuthController::redirectRegister
     */
    public function testIndexRedirectRegisterNestedInfo(): void
    {
        $accessToken = $this->createMock(AccessToken::class);
        $this->setExpects($accessToken, 'getToken', null, 'test-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getRefreshToken', null, 'test-refresh-token', $this->atLeastOnce());
        $this->setExpects($accessToken, 'getExpires', null, 4242424242, $this->atLeastOnce());

        $config = $this->config->get('oauth');
        $config['testprovider'] = array_merge($config['testprovider'], [
            'nested_info' => true,
            'id'          => 'nested.id',
            'email'       => 'nested.email',
            'username'    => 'nested.name',
            'first_name'  => 'nested.first',
            'last_name'   => 'nested.last',
        ]);
        $this->config->set('oauth', $config);

        $this->config->set('registration_enabled', true);

        /** @var ResourceOwnerInterface|MockObject $resourceOwner */
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->setExpects($resourceOwner, 'getId', null, null, $this->never());
        $this->setExpects(
            $resourceOwner,
            'toArray',
            null,
            [
                'nested' => [
                    'id'    => 42, // new provider user identifier
                    'name'  => 'testuser',
                    'email' => 'foo.bar@localhost',
                    'first' => 'Test',
                    'last'  => 'Tester',
                ],
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

        $controller->index($request);
        $this->assertEquals('testuser', $this->session->get('form-data-username'));
        $this->assertEquals('foo.bar@localhost', $this->session->get('form-data-email'));
        $this->assertEquals('Test', $this->session->get('form-data-firstname'));
        $this->assertEquals('Tester', $this->session->get('form-data-lastname'));
    }


    /**
     * @covers \Engelsystem\Controllers\OAuthController::connect
     * @covers \Engelsystem\Controllers\OAuthController::requireProvider
     * @covers \Engelsystem\Controllers\OAuthController::isValidProvider
     */
    public function testConnect(): void
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
    public function testDisconnectIfAllowIsTrue(): void
    {
        $oauthConfig = $this->config->get('oauth');
        $oauthConfig['testprovider']['allow_user_disconnect'] = true;

        $this->runDisconnectTest($oauthConfig, true);
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::disconnect
     */
    public function testDisconnectIfAllowIsNull(): void
    {
        $oauthConfig = $this->config->get('oauth');
        $oauthConfig['testprovider']['allow_user_disconnect'] = null;

        $this->runDisconnectTest($oauthConfig, true);
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::disconnect
     */
    public function testDisconnectIfAllowIsUnset(): void
    {
        $oauthConfig = $this->config->get('oauth');
        unset($oauthConfig['testprovider']['allow_user_disconnect']);

        $this->runDisconnectTest($oauthConfig, true);
    }

    /**
     * @covers \Engelsystem\Controllers\OAuthController::disconnect
     */
    public function testDisconnectIfAllowIsFalse(): void
    {
        $oauthConfig = $this->config->get('oauth');
        $oauthConfig['testprovider']['allow_user_disconnect'] = false;

        $this->runDisconnectTest($oauthConfig, false);
    }

    private function runDisconnectTest(mixed $oauthConfig, bool $shouldDisconnect): void
    {
        $this->config->set('oauth', $oauthConfig);

        $controller = $this->getMock(['addNotification']);
        $request = (new Request())->withAttribute('provider', 'testprovider');

        if (!$shouldDisconnect) {
            $this->expectException(HttpNotFound::class);
            $controller->disconnect($request);

            return; // Should never happen, creates cleaner errors
        }

        $this->setExpects($controller, 'addNotification', ['oauth.disconnected']);
        $this->setExpects($this->auth, 'user', null, $this->authenticatedUser);
        $this->setExpects($this->redirect, 'back', null, new Response());

        $controller->disconnect($request);

        $this->assertCount(1, OAuth::all());
        $this->assertTrue($this->log->hasInfoThatContains('Disconnected'));
    }

    protected function getMock(array $mockMethods = []): OAuthController | MockObject
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
                $this->url,
            ])
            ->onlyMethods($mockMethods)
            ->getMock();

        return $controller;
    }

    /**
     * Set up the DB
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
