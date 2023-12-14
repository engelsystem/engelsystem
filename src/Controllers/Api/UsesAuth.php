<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;

trait UsesAuth
{
    protected ?Authenticator $auth = null;

    public function setAuth(Authenticator $auth): void
    {
        $this->auth = $auth;
    }

    protected function getUser(int|string $userId): ?User
    {
        if ($userId == 'self' && $this->auth) {
            return $this->auth->user();
        }

        return User::findOrFail($userId);
    }
}
