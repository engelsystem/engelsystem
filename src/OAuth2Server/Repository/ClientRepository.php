<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\Models\OAuth2Client;
use Engelsystem\OAuth2Server\Entity\ClientEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(protected OAuth2Client $client)
    {
    }

    public function getClientEntity(mixed $clientIdentifier): ?ClientEntity
    {
        $client = $this->client
            ->where('identifier', (string) $clientIdentifier)
            ->where('active', true)
            ->first();

        if (!$client) {
            return null;
        }

        return new ClientEntity($client);
    }

    public function validateClient(mixed $clientIdentifier, mixed $clientSecret, mixed $grantType): bool
    {
        $entity = $this->getClientEntity($clientIdentifier);

        if (!$entity) {
            return false;
        }

        $model = $entity->getModel();

        // Check grant type is allowed
        if ($grantType !== null && !$model->hasGrant($grantType)) {
            return false;
        }

        // Public clients don't need secret validation
        if (!$model->confidential) {
            return true;
        }

        // Validate secret for confidential clients
        if ($clientSecret === null) {
            return false;
        }

        return password_verify($clientSecret, $model->secret ?? '');
    }
}
