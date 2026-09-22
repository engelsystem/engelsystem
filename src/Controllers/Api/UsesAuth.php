<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Psr\Http\Message\ServerRequestInterface;

trait UsesAuth
{
    protected ?Authenticator $auth = null;

    public function setAuth(Authenticator $auth): void
    {
        $this->auth = $auth;
    }

    /**
     * Requests for the authenticated user ("self") don't require the "api" privilege,
     * they only require a valid, authenticated user. Requests for a numeric user id
     * fall back to the controller's regular permission check.
     */
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        if ($request->getAttribute('user_id') !== 'self') {
            return null;
        }

        return (bool) $this->auth?->user();
    }

    protected function getUser(int|string $userId): ?User
    {
        if ($userId == 'self' && $this->auth) {
            return $this->auth->user();
        }

        return User::findOrFail($userId);
    }
}
