<?php

declare(strict_types=1);

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
use Engelsystem\Models\User\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface as ResourceOwner;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session as Session;

class OAuthController extends BaseController
{
    use HasUserNotifications;

    public function __construct(
        protected Authenticator $auth,
        protected AuthController $authController,
        protected Config $config,
        protected LoggerInterface $log,
        protected OAuth $oauth,
        protected Redirector $redirect,
        protected Session $session,
        protected UrlGenerator $url
    ) {
    }

    /**
     * Apply optional username transformation to make usernames from OAuth provider
     * compatible with username requirements.
     *
     * @param string $username The original (non-transformed) OAuth username
     * @return string The length-capped and transformed username
     */
    private function transformUserName(string $username): string
    {
        $pattern = $this->config->get('username_sync_pattern', null);
        $replace = $this->config->get('username_sync_replace', null);

        if ($pattern !== null && $replace !== null) {
            $username = preg_replace($pattern, $replace, $username);
        } elseif ($pattern !== null || $replace !== null) {
            $this->log->error(
                'username_sync_pattern or replace is set, but not both. Ignoring transformation.',
                ['username' => $username, 'pattern' => $pattern, 'replace' => $replace]
            );
        }

        return substr($username, 0, 24);
    }

    private function checkForUpdate(
        User $user,
        array $config,
        string $key,
        string|null $currentValue,
        Collection $userdata,
        mixed $transformer = null
    ): mixed {
        if (in_array($key, $config['sso_fields_to_sync'] ?? [])) {
            if (!$userdata->has($config[$key])) {
                $this->log->error(
                    'Cannot update {key} for user ID {id}: SSO field missing - fix your SSO config!',
                    [
                        'key' => $key,
                        'id' => $user->id,
                    ]
                );

                return [$currentValue, false];
            }

            $rawNewValue = $userdata[$config[$key]];
            $newValue = $transformer ? $transformer($rawNewValue) : $rawNewValue;

            if ($currentValue != $newValue) {
                $this->log->info(
                    'Updating {key} for user ID {id} from {old} to {new} via SSO sync',
                    [
                        'key' => $key,
                        'id' => $user->id,
                        'old' => $currentValue,
                        'new' => $newValue,
                    ]
                );

                return [$newValue, true];
            }
        }
        return [$currentValue, false];
    }

    private function updateUserDataFromSSO(User $user, array $config, Collection $userdata): void
    {
        [$user->name, $nameUpdated] =
            $this->checkForUpdate($user, $config, 'username', $user->name, $userdata, [$this, 'transformUserName']);

        [$user->email, $emailUpdated] = $this->checkForUpdate($user, $config, 'email', $user->email, $userdata);

        [$user->personalData->first_name, $firstNameUpdated] =
            $this->checkForUpdate($user, $config, 'first_name', $user->personalData->first_name, $userdata);

        [$user->personalData->last_name, $lastNameUpdated] =
            $this->checkForUpdate($user, $config, 'last_name', $user->personalData->last_name, $userdata);

        if ($nameUpdated || $emailUpdated) {
            $user->save();
        }

        if ($firstNameUpdated || $lastNameUpdated) {
            $user->personalData->save();
        }
    }

    public function index(Request $request): Response
    {
        $providerName = $request->getAttribute('provider');

        $provider = $this->getProvider($providerName);
        $config = $this->config->get('oauth')[$providerName];

        // Handle OAuth error response according to https://www.rfc-editor.org/rfc/rfc6749#section-4.1.2.1
        if ($request->has('error')) {
            throw new HttpNotFound('oauth.' . $request->get('error'));
        }

        // Initial request redirects to provider
        if (!$request->has('code')) {
            $authorizationUrl = $provider->getAuthorizationUrl(
                [
                    // League oauth separates scopes by comma, which is wrong, so we do it
                    // here properly by spaces. See https://www.rfc-editor.org/rfc/rfc6749#section-3.3
                    'scope' => join(' ', $config['scope'] ?? []),
                ]
            );
            $this->session->set('oauth2_state', $provider->getState());

            return $this->redirect->to($authorizationUrl);
        }

        // Redirected URL got called a second time
        if (
            !$this->session->get('oauth2_state')
            || $request->get('state') !== $this->session->get('oauth2_state')
        ) {
            $this->session->remove('oauth2_state');

            $this->log->warning('Invalid OAuth state');

            throw new HttpNotFound('oauth.invalid-state');
        }

        // Fetch access token
        $accessToken = null;
        try {
            $accessToken = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $request->get('code'),
                ]
            );
        } catch (IdentityProviderException $e) {
            $this->handleOAuthError($e, $providerName);
        }

        // Load resource identifier
        $resourceOwner = null;
        try {
            $resourceOwner = $provider->getResourceOwner($accessToken);
        } catch (IdentityProviderException $e) {
            $this->handleOAuthError($e, $providerName);
        }
        $resourceId = $this->getId($providerName, $resourceOwner);

        // Fetch existing oauth state
        /** @var OAuth|null $oauth */
        $oauth = $this->oauth
            ->query()
            ->where('provider', $providerName)
            ->where('identifier', $resourceId)
            ->get()
            // Explicit case-sensitive comparison using PHP as some DBMS collations are case-sensitive and some aren't
            ->where('identifier', '===', (string) $resourceId)
            ->first();

        // Update oauth state
        $expirationTime = $accessToken->getExpires();
        $expirationTime = $expirationTime
            ? Carbon::createFromTimestamp($expirationTime, Carbon::now()->timezone)
            : null;
        if ($oauth) {
            $oauth->access_token = $accessToken->getToken();
            $oauth->refresh_token = $accessToken->getRefreshToken();
            $oauth->expires_at = $expirationTime;

            $oauth->save();
        }

        // Load user
        $user = $this->auth->user();
        if ($oauth && $user && $user->id != $oauth->user_id) {
            throw new HttpNotFound('oauth.already-connected');
        }

        $connectProvider = $this->session->get('oauth2_connect_provider');
        $this->session->remove('oauth2_connect_provider');
        // Connect user with oauth
        if (!$oauth && $user && $connectProvider && $connectProvider == $providerName) {
            $oauth = new OAuth([
                'provider'      => $providerName,
                'identifier'    => $resourceId,
                'access_token'  => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expires_at'    => $expirationTime,
            ]);

            try {
                $oauth->user()
                    ->associate($user)
                    ->save();
            // @codeCoverageIgnoreStart
            } catch (UniqueConstraintViolationException) {
                $this->log->error(
                    'Duplicate OAuth user {user} using {provider}: Database does not support unique with mixed case! ',
                    ['provider' => $providerName, 'user' => $resourceId]
                );
                throw new HttpNotFound('oauth.provider-error');
                // @codeCoverageIgnoreEnd
            }

            $this->log->info(
                'Connected OAuth user {user} using {provider}',
                ['provider' => $providerName, 'user' => $resourceId]
            );
            $this->addNotification('oauth.connected');
        }

        // Load user data
        $resourceData = $resourceOwner->toArray();
        if (!empty($config['nested_info'])) {
            $resourceData = Arr::dot($resourceData);
        }

        $userdata = new Collection($resourceData);
        if (!$oauth) {
            // User authenticated but has no account
            return $this->redirectRegister(
                $providerName,
                (string) $resourceId,
                $accessToken,
                $config,
                $userdata
            );
        }

        if (isset($config['mark_arrived']) && $config['mark_arrived']) {
            $this->handleArrive($providerName, $oauth, $resourceOwner);
        }

        $this->updateUserDataFromSSO($oauth->user, $config, $userdata);
        $response = $this->authController->loginUser($oauth->user);
        event('oauth2.login', ['provider' => $providerName, 'data' => $userdata]);

        return $response;
    }

    public function connect(Request $request): Response
    {
        $providerName = $request->getAttribute('provider');

        $this->requireProvider($providerName);

        $this->session->set('oauth2_connect_provider', $providerName);

        return $this->index($request);
    }

    public function disconnect(Request $request): Response
    {
        $providerName = $request->getAttribute('provider');

        $this->requireProvider($providerName);

        if (!($this->config->get('oauth')[$providerName]['allow_user_disconnect'] ?? true)) {
            throw new HttpNotFound();
        }

        $this->oauth
            ->whereUserId($this->auth->user()->id)
            ->where('provider', $providerName)
            ->delete();

        $this->log->info('Disconnected OAuth from {provider}', ['provider' => $providerName]);
        $this->addNotification('oauth.disconnected');

        return $this->redirect->back();
    }

    protected function getProvider(string $name): AbstractProvider
    {
        $this->requireProvider($name);
        $config = $this->config->get('oauth')[$name];

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

    protected function getId(string $providerName, ResourceOwner $resourceOwner): mixed
    {
        $config = $this->config->get('oauth')[$providerName];
        if (empty($config['nested_info'])) {
            return $resourceOwner->getId();
        }

        $data = Arr::dot($resourceOwner->toArray());
        return $data[$config['id']];
    }

    protected function requireProvider(string $provider): void
    {
        if (!$this->isValidProvider($provider)) {
            throw new HttpNotFound('oauth.provider-not-found');
        }
    }

    protected function isValidProvider(string $name): bool
    {
        $config = $this->config->get('oauth');

        return isset($config[$name]);
    }

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

        $userState->arrival_date = new Carbon();
        $userState->save();

        $this->log->info(
            'Set user {name} ({id}) as arrived via {provider} user {user}',
            [
                'provider' => $providerName,
                'user'     => $this->getId($providerName, $resourceOwner),
                'name'     => $user->name,
                'id'       => $user->id,
            ]
        );
    }

    /**
     *
     * @throws HttpNotFound
     */
    protected function handleOAuthError(IdentityProviderException $e, string $providerName): void
    {
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

    protected function redirectRegister(
        string $providerName,
        string $providerUserIdentifier,
        AccessTokenInterface $accessToken,
        array $config,
        Collection $userdata
    ): Response {
        $config = array_merge(
            [
                'username'             => null,
                'email'                => null,
                'first_name'           => null,
                'last_name'            => null,
                'enable_password'      => false,
                'allow_registration'   => null,
                'groups'               => null,
                'sso_fields_to_sync'   => [],
            ],
            $config
        );

        if (!$this->config->get('registration_enabled') && !$config['allow_registration']) {
            throw new HttpNotFound('oauth.not-found');
        }

        // Set registration form field data
        $username = $this->transformUserName($userdata->get($config['username']));
        $this->session->set('form-data-username', $username);

        $this->session->set('form-data-email', $userdata->get($config['email']));
        $this->session->set('form-data-firstname', $userdata->get($config['first_name']));
        $this->session->set('form-data-lastname', $userdata->get($config['last_name']));

        // Additionally keep user information from SSO in session
        $fieldsToSync = $config['sso_fields_to_sync'] ?? [];
        foreach (['username', 'email', 'first_name', 'last_name'] as $field) {
            $this->session->set('form-data-sso-sync-' . $field, in_array($field, $fieldsToSync));
        }

        if (in_array('username', $fieldsToSync)) {
            $this->session->set('oauth2_data_username', $username);
        }
        if (in_array('email', $fieldsToSync)) {
            $this->session->set('oauth2_data_email', $userdata->get($config['email']));
        }
        if (in_array('first_name', $fieldsToSync)) {
            $this->session->set('oauth2_data_firstname', $userdata->get($config['first_name']));
        }
        if (in_array('last_name', $fieldsToSync)) {
            $this->session->set('oauth2_data_lastname', $userdata->get($config['last_name']));
        }

        // Define OAuth state
        $this->session->set('oauth2_groups', $userdata->get($config['groups'], []));
        $this->session->set('oauth2_connect_provider', $providerName);
        $this->session->set('oauth2_user_id', $providerUserIdentifier);

        $timezone = Carbon::now()->timezone;
        $expirationTime = $accessToken->getExpires();
        $expirationTime = $expirationTime ? Carbon::createFromTimestamp($expirationTime, $timezone) : null;
        $this->session->set('oauth2_access_token', $accessToken->getToken());
        $this->session->set('oauth2_refresh_token', $accessToken->getRefreshToken());
        $this->session->set('oauth2_expires_at', $expirationTime);
        $this->session->set('oauth2_enable_password', $config['enable_password']);
        $this->session->set('oauth2_allow_registration', $config['allow_registration']);

        return $this->redirect->to('/register');
    }
}
