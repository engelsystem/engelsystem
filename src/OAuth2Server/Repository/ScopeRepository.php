<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\OAuth2Server\Entity\ClientEntity;
use Engelsystem\OAuth2Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Available scopes and their descriptions
     */
    public const SCOPES = [
        'openid' => 'OpenID Connect identifier',
        'profile' => 'User profile (name, username, pronoun)',
        'email' => 'Email address',
        'angeltypes' => 'Angel type memberships',
        'groups' => 'User permission groups',
    ];

    public function getScopeEntityByIdentifier(mixed $identifier): ?ScopeEntityInterface
    {
        if (!array_key_exists((string) $identifier, self::SCOPES)) {
            return null;
        }

        return new ScopeEntity((string) $identifier);
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        mixed $grantType,
        ClientEntityInterface $clientEntity,
        mixed $userIdentifier = null,
        ?string $authCodeId = null
    ): array {
        // Filter scopes to only those allowed by the client
        if ($clientEntity instanceof ClientEntity) {
            $allowedScopes = $clientEntity->getModel()->scopes;

            if ($allowedScopes !== null) {
                $scopes = array_filter(
                    $scopes,
                    fn(ScopeEntityInterface $scope) => in_array($scope->getIdentifier(), $allowedScopes, true)
                );
            }
        }

        return array_values($scopes);
    }

    /**
     * Get all available scopes with descriptions
     *
     * @return array<string, string>
     */
    public function getAvailableScopes(): array
    {
        return self::SCOPES;
    }
}
