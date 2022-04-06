<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Controllers\idp\AccessTokenRepository;
use Engelsystem\Controllers\idp\AuthCodeRepository;
use Engelsystem\Controllers\idp\ClientRepository;
use Engelsystem\Controllers\idp\RefreshTokenRepository;
use Engelsystem\Controllers\idp\ScopeRepository;
use Engelsystem\Controllers\idp\UserEntity;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Renderer\Twig\Extensions\Session;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;


class OAuthIDPController extends BaseController
{
    use HasUserNotifications;

    /** @var Authenticator */
    protected $auth;


    /** @var LoggerInterface */
    protected $log;

    /** @var Redirector */
    protected $redirector;

    /** @var Session */
    protected $session;

    /** @var Container */
    protected $response;

    /** @var AuthorizationServer|null */
    private $server;


    /**
     * @param Authenticator $auth
     * @param Config $config
     * @param LoggerInterface $log
     * @param Redirector $redirector
     * @param Session $session
     * @param Response $response
     */
    public function __construct(
        Authenticator   $auth,
        Config          $config,
        LoggerInterface $log,
        Redirector      $redirector,
        Session         $session,
        Response        $response
    )
    {
        $this->auth = $auth;
        $this->log = $log;
        $this->redirector = $redirector;
        $this->session = $session;
        $this->response = $response;

        $idpConfig = $config->get('oauthidp');
        if ($idpConfig == null) {
            return;
        }

        $clientRepository = new ClientRepository($idpConfig["clients"]);
        $accessTokenRepository = new AccessTokenRepository();
        $scopeRepository = new ScopeRepository();
        $authCodeRepository = new AuthCodeRepository();
        $refreshTokenRepository = new RefreshTokenRepository();

        $this->server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $idpConfig["privateKeyPath"],
            $idpConfig["secret"]
        );

        $this->server->enableGrantType(new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT10M')
        ), new \DateInterval('PT1H'));

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // The refresh token will expire in 1 month

        $this->server->enableGrantType(
            $grant,
            new \DateInterval('PT1H') // The new access token will expire after 1 hour
        );
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function authorize(Request $request): ResponseInterface
    {
        if ($this->server === null) {
            throw new HttpNotFound();
        }

        $user = $this->auth->user();
        if (!$user) {
            throw new HttpForbidden();
        }

        $authRequest = $this->server->validateAuthorizationRequest($request);
        $idpUser = new UserEntity();
        $idpUser->setIdentifier($user->id);
        $authRequest->setUser($idpUser);
        $authRequest->setAuthorizationApproved(true);

        return $this->server->completeAuthorizationRequest($authRequest, $this->response);
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function accessToken(Request $request): ResponseInterface
    {
        if ($this->server === null) {
            throw new HttpNotFound();
        }

        return $this->server->respondToAccessTokenRequest($request, $this->response);
    }
}


