<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\Models\OAuth2AuthCode;
use Engelsystem\OAuth2Server\Entity\AuthCodeEntity;
use Engelsystem\OAuth2Server\Entity\ClientEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(protected OAuth2AuthCode $authCode)
    {
    }

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $client = $authCodeEntity->getClient();
        $clientId = null;

        if ($client instanceof ClientEntity) {
            $clientId = $client->getModel()->id;
        }

        $this->authCode->create([
            'id' => $authCodeEntity->getIdentifier(),
            'oauth2_client_id' => $clientId,
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'scopes' => array_map(
                fn(ScopeEntityInterface $scope) => $scope->getIdentifier(),
                $authCodeEntity->getScopes()
            ),
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
            'redirect_uri' => $authCodeEntity->getRedirectUri(),
            'revoked' => false,
        ]);
    }

    public function revokeAuthCode(mixed $codeId): void
    {
        $code = $this->authCode->find($codeId);

        if ($code) {
            $code->revoke();
        }
    }

    public function isAuthCodeRevoked(mixed $codeId): bool
    {
        $code = $this->authCode->find($codeId);

        return $code === null || $code->revoked;
    }
}
