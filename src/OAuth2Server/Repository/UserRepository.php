<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Repository;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\OAuth2Server\Entity\UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $user,
        protected Authenticator $auth
    ) {
    }

    public function getUserEntityByUserCredentials(
        mixed $username,
        mixed $password,
        mixed $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        // For authorization code flow, we don't use password grant
        // This method is only used for password grant which we don't support
        return null;
    }

    /**
     * Get a user entity by user ID
     */
    public function getUserById(int $userId): ?UserEntity
    {
        $user = $this->user->find($userId);

        if (!$user) {
            return null;
        }

        return new UserEntity($user);
    }
}
