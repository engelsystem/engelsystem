<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\OAuth;
use Illuminate\Support\Collection;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Bahuma\OAuth2\Client\Provider\Nextcloud;
use League\OAuth2\Client\Provider\ResourceOwnerInterface as ResourceOwner;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session as Session;

class OAuthController extends BaseController
{
    use HasUserNotifications;

    /** @var Authenticator */
    protected $auth;

    /** @var AuthController */
    protected $authController;

    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $log;

    /** @var OAuth */
    protected $oauth;

    /** @var Redirector */
    protected $redirector;

    /** @var Session */
    protected $session;

    /** @var UrlGenerator */
    protected $url;

    /**
     * @param Authenticator   $auth
     * @param AuthController  $authController
     * @param Config          $config
     * @param LoggerInterface $log
     * @param OAuth           $oauth
     * @param Redirector      $redirector
     * @param Session         $session
     * @param UrlGenerator    $url
     */
    public function __construct(
        Authenticator $auth,
        AuthController $authController,
        Config $config,
        LoggerInterface $log,
        OAuth $oauth,
        Redirector $redirector,
        Session $session,
        UrlGenerator $url
    ) {
        $this->auth = $auth;
        $this->authController = $authController;
        $this->config = $config;
        $this->log = $log;
        $this->redirector = $redirector;
        $this->oauth = $oauth;
        $this->session = $session;
        $this->url = $url;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $providerName = $request->getAttribute('provider');
        $provider = $this->getProvider($providerName);

        if (!$request->has('code')) {
            $authorizationUrl = $provider->getAuthorizationUrl();
            $this->session->set('oauth2_state', $provider->getState());

            return $this->redirector->to($authorizationUrl);
        }

        if (
            !$this->session->get('oauth2_state')
            || $request->get('state') !== $this->session->get('oauth2_state')
        ) {
            $this->session->remove('oauth2_state');

            $this->log->warning('Invalid OAuth state');

            throw new HttpNotFound('oauth.invalid-state');
        }

        try {
            $accessToken = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $request->get('code')
                ]
            );
        } catch (IdentityProviderException $e) {
            $response = $e->getResponseBody();
            $response = is_array($response) ? json_encode($response) : $response;
            $this->log->error(
                '{provider} identity provider error: {error} {description}',
                [
                    'provider'    => $providerName,
                    'error'       => $e->getMessage(),
                    'description' => $response,
                ]
            );

            throw new HttpNotFound('oauth.provider-error');
        }

        $resourceOwner = $provider->getResourceOwner($accessToken);
        $resourceId = $resourceOwner->getId();

        /** @var OAuth|null $oauth */
        $oauth = $this->oauth
            ->query()
            ->where('provider', $providerName)
            ->where('identifier', $resourceId)
            ->get()
            // Explicit case sensitive comparison using PHP as some DBMS collations are case sensitive and some arent
            ->where('identifier', '===', $resourceId)
            ->first();

        $expirationTime = $accessToken->getExpires();
        $expirationTime = $expirationTime ? Carbon::createFromTimestamp($expirationTime) : null;
        if ($oauth) {
            $oauth->access_token = $accessToken->getToken();
            $oauth->refresh_token = $accessToken->getRefreshToken();
            $oauth->expires_at = $expirationTime;

            $oauth->save();
        }

        $user = $this->auth->user();
        if ($oauth && $user && $user->id != $oauth->user_id) {
            throw new HttpNotFound('oauth.already-connected');
        }

        $connectProvider = $this->session->get('oauth2_connect_provider');
        $this->session->remove('oauth2_connect_provider');
        if (!$oauth && $user && $connectProvider && $connectProvider == $providerName) {
            $oauth = new OAuth([
                'provider'      => $providerName,
                'identifier'    => $resourceOwner->getId(),
                'access_token'  => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expires_at'    => $expirationTime,
            ]);
            $oauth->user()
                ->associate($user)
                ->save();

            $this->log->info(
                'Connected OAuth user {user} using {provider}',
                ['provider' => $providerName, 'user' => $resourceOwner->getId()]
            );
            $this->addNotification('oauth.connected');
        }

        if (str_contains($providerName, 'nextcloud')) {
            $userdata = new Collection([
                'email' => $resourceOwner->getEmail(),
            ]);
            $config = [];
        } else {
            $userdata = new Collection($resourceOwner->toArray());
            $config = $this->config->get('oauth')[$providerName];
        }

        if (!$oauth) {
            return $this->redirectRegisterOrThrowNotFound(
                $providerName,
                $resourceOwner->getId(),
                $accessToken,
                $config,
                $userdata
            );
        }

        if (isset($config['mark_arrived']) && $config['mark_arrived']) {
            $this->handleArrive($providerName, $oauth, $resourceOwner);
        }

        return $this->authController->loginUser($oauth->user);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function connect(Request $request): Response
    {
        $provider = $request->getAttribute('provider');
        $this->requireProvider($provider);

        $this->session->set('oauth2_connect_provider', $provider);

        return $this->index($request);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function disconnect(Request $request): Response
    {
        $provider = $request->getAttribute('provider');

        $this->oauth
            ->whereUserId($this->auth->user()->id)
            ->where('provider', $provider)
            ->delete();

        $this->log->info('Disconnected OAuth from {provider}', ['provider' => $provider]);
        $this->addNotification('oauth.disconnected');

        return $this->redirector->back();
    }

    /**
     * @param string $name
     *
     * @return AbstractProvider
     */
    protected function getProvider(string $name): AbstractProvider
    {
        $this->requireProvider($name);
        $config = $this->config->get('oauth')[$name];

        if (str_contains($name, "nextcloud")) {
            return new Nextcloud(
                [
                    'clientId'     => $config['client_id'],
                    'clientSecret' => $config['client_secret'],
                    'redirectUri'  => $this->url->to('oauth/' . $name),
                    'nextcloudUrl' => $config['url_nextcloud'],
                ]
            );
        } else {
            return new GenericProvider(
                [
                    'clientId'                => $config['client_id'],
                    'clientSecret'            => $config['client_secret'],
                    'redirectUri'             => $this->url->to('oauth/' . $name),
                    'urlAuthorize'            => $config['url_auth'],
                    'urlAccessToken'          => $config['url_token'],
                    'urlResourceOwnerDetails' => $config['url_info'],
                    'responseResourceOwnerId' => $config['id'],
                ]
            );
        }
    }

    /**
     * @param string $provider
     */
    protected function requireProvider(string $provider): void
    {
        if (!$this->isValidProvider($provider)) {
            throw new HttpNotFound('oauth.provider-not-found');
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function isValidProvider(string $name): bool
    {
        $config = $this->config->get('oauth');

        return isset($config[$name]);
    }

    /**
     * @param OAuth         $auth
     * @param string        $providerName
     * @param ResourceOwner $resourceOwner
     */
    protected function handleArrive(
        string $providerName,
        OAuth $auth,
        ResourceOwner $resourceOwner
    ): void {
        $user = $auth->user;
        $userState = $user->state;

        if ($userState->arrived) {
            return;
        }

        $userState->arrived = true;
        $userState->arrival_date = new Carbon();
        $userState->save();

        $this->log->info(
            'Set user {name} ({id}) as arrived via {provider} user {user}',
            [
                'provider' => $providerName,
                'user'     => $resourceOwner->getId(),
                'name'     => $user->name,
                'id'       => $user->id
            ]
        );
    }

    /**
     * @param string               $providerName
     * @param string               $providerUserIdentifier
     * @param AccessTokenInterface $accessToken
     * @param array                $config
     * @param Collection           $userdata
     *
     * @return Response
     */
    protected function redirectRegisterOrThrowNotFound(
        string $providerName,
        string $providerUserIdentifier,
        AccessTokenInterface $accessToken,
        array $config,
        Collection $userdata
    ): Response {
        if (!$this->config->get('registration_enabled')) {
            throw new HttpNotFound('oauth.not-found');
        }

        if (str_contains($providerName, 'nextcloud')) {
            $this->session->set(
                'form_data',
                [
                    'name'          => $providerUserIdentifier,
                    'email'         => $userdata->get('email'),
                ],
            );
        } else {
            $config = array_merge(
                ['username' => null, 'email' => null, 'first_name' => null, 'last_name' => null],
                $config
            );
            $this->session->set(
                'form_data',
                [
                    'name'       => $userdata->get($config['username']),
                    'email'      => $userdata->get($config['email']),
                    'first_name' => $userdata->get($config['first_name']),
                    'last_name'  => $userdata->get($config['last_name']),
                ],
            );
        }
        $this->session->set('oauth2_connect_provider', $providerName);
        $this->session->set('oauth2_user_id', $providerUserIdentifier);

        $expirationTime = $accessToken->getExpires();
        $expirationTime = $expirationTime ? Carbon::createFromTimestamp($expirationTime) : null;
        $this->session->set('oauth2_access_token', $accessToken->getToken());
        $this->session->set('oauth2_refresh_token', $accessToken->getRefreshToken());
        $this->session->set('oauth2_expires_at', $expirationTime);

        return $this->redirector->to('/register');
    }
}
