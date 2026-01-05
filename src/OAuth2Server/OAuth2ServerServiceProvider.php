<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server;

use DateInterval;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\OAuth2Server\Repository\AccessTokenRepository;
use Engelsystem\OAuth2Server\Repository\AuthCodeRepository;
use Engelsystem\OAuth2Server\Repository\ClientRepository;
use Engelsystem\OAuth2Server\Repository\RefreshTokenRepository;
use Engelsystem\OAuth2Server\Repository\ScopeRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;

class OAuth2ServerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        // Check if OAuth2 server is enabled
        if (!$config->get('oauth2_server.enabled', false)) {
            return;
        }

        $this->registerAuthorizationServer($config);
        $this->registerResourceServer($config);
    }

    protected function registerAuthorizationServer(Config $config): void
    {
        $privateKeyPath = $config->get('oauth2_server.private_key');
        $encryptionKey = $config->get('oauth2_server.encryption_key');

        if (!$privateKeyPath || !$encryptionKey) {
            return;
        }

        // Create repositories
        $clientRepository = $this->app->make(ClientRepository::class);
        $accessTokenRepository = $this->app->make(AccessTokenRepository::class);
        $scopeRepository = $this->app->make(ScopeRepository::class);
        $authCodeRepository = $this->app->make(AuthCodeRepository::class);
        $refreshTokenRepository = $this->app->make(RefreshTokenRepository::class);

        // Create authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            new CryptKey($privateKeyPath, null, false),
            $encryptionKey
        );

        // Configure auth code grant
        $authCodeTtl = new DateInterval('PT10M'); // 10 minutes
        $authCodeGrant = new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            $authCodeTtl
        );

        $refreshTokenTtl = $config->get('oauth2_server.refresh_token_ttl', 2592000); // 30 days
        $authCodeGrant->setRefreshTokenTTL(new DateInterval('PT' . $refreshTokenTtl . 'S'));

        $accessTokenTtl = $config->get('oauth2_server.access_token_ttl', 3600); // 1 hour
        $server->enableGrantType(
            $authCodeGrant,
            new DateInterval('PT' . $accessTokenTtl . 'S')
        );

        // Configure refresh token grant
        $refreshTokenGrant = new RefreshTokenGrant($refreshTokenRepository);
        $refreshTokenGrant->setRefreshTokenTTL(new DateInterval('PT' . $refreshTokenTtl . 'S'));

        $server->enableGrantType(
            $refreshTokenGrant,
            new DateInterval('PT' . $accessTokenTtl . 'S')
        );

        $this->app->instance(AuthorizationServer::class, $server);
    }

    protected function registerResourceServer(Config $config): void
    {
        $publicKeyPath = $config->get('oauth2_server.public_key');

        if (!$publicKeyPath) {
            return;
        }

        $accessTokenRepository = $this->app->make(AccessTokenRepository::class);

        $server = new ResourceServer(
            $accessTokenRepository,
            new CryptKey($publicKeyPath, null, false)
        );

        $this->app->instance(ResourceServer::class, $server);
    }
}
