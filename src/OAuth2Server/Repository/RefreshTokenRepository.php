<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\Models\OAuth2RefreshToken;
use Engelsystem\OAuth2Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(protected OAuth2RefreshToken $refreshToken)
    {
    }

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this->refreshToken->create([
            'id' => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
            'revoked' => false,
        ]);
    }

    public function revokeRefreshToken(mixed $tokenId): void
    {
        $token = $this->refreshToken->find($tokenId);

        if ($token) {
            $token->revoke();
        }
    }

    public function isRefreshTokenRevoked(mixed $tokenId): bool
    {
        $token = $this->refreshToken->find($tokenId);

        if ($token === null) {
            return true;
        }

        if ($token->revoked) {
            return true;
        }

        // Also check if the associated access token is revoked
        $accessToken = $token->accessToken;

        return $accessToken === null || $accessToken->revoked;
    }
}
