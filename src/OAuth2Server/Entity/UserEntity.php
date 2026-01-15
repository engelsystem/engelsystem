<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Entity;

use Engelsystem\Models\User\User;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{
    public function __construct(protected User $user)
    {
    }

    public function getIdentifier(): string
    {
        return (string) $this->user->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
