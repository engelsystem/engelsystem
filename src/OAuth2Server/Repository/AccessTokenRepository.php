<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\Models\OAuth2AccessToken;
use Engelsystem\OAuth2Server\Entity\AccessTokenEntity;
use Engelsystem\OAuth2Server\Entity\ClientEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(protected OAuth2AccessToken $accessToken)
    {
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        mixed $userIdentifier = null
    ): AccessTokenEntityInterface {
        $token = new AccessTokenEntity();
        $token->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }

        if ($userIdentifier !== null) {
            $token->setUserIdentifier($userIdentifier);
        }

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $client = $accessTokenEntity->getClient();
        $clientId = null;

        if ($client instanceof ClientEntity) {
            $clientId = $client->getModel()->id;
        }

        $this->accessToken->create([
            'id' => $accessTokenEntity->getIdentifier(),
            'oauth2_client_id' => $clientId,
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'scopes' => array_map(
                fn(ScopeEntityInterface $scope) => $scope->getIdentifier(),
                $accessTokenEntity->getScopes()
            ),
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
            'revoked' => false,
        ]);
    }

    public function revokeAccessToken(mixed $tokenId): void
    {
        $token = $this->accessToken->find($tokenId);

        if ($token) {
            $token->revoke();
        }
    }

    public function isAccessTokenRevoked(mixed $tokenId): bool
    {
        $token = $this->accessToken->find($tokenId);

        return $token === null || $token->revoked;
    }
}
