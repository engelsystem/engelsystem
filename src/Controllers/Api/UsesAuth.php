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
     * API access concept (see resources/api/readme.md):
     *
     * - The "api" privilege grants read access (GET) to every endpoint. It is read-only and never
     *   grants write access.
     * - Without it, an action is allowed if the user has the web privilege that grants the same
     *   access on the website (see webPermission()). Write actions always require that privilege.
     * - Requests for the authenticated user ("self") only require an authenticated user.
     */
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        if (!$this->auth?->user()) {
            return false;
        }

        if ($this->isReadRequest($request) && $this->auth->can('api')) {
            return true;
        }

        $webPermission = $request->getAttribute('user_id') === 'self'
            ? true
            : $this->webPermission($method);

        if (is_bool($webPermission) || is_null($webPermission)) {
            return (bool) $webPermission;
        }

        return $this->auth->can($webPermission);
    }

    /**
     * The web privilege(s) granting access to the given action without the "api" privilege:
     * true for any authenticated user, null if the action is only available with "api".
     *
     * @return string|string[]|bool|null
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return null;
    }

    /**
     * Whether the user has the (read-only) "api" privilege, which keeps access to all data the API
     * exposed before web privileges were taken into account.
     */
    protected function hasApiPrivilege(): bool
    {
        return (bool) $this->auth?->can('api');
    }

    protected function isReadRequest(ServerRequestInterface $request): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']);
    }

    protected function getUser(int|string $userId): ?User
    {
        if ($userId == 'self' && $this->auth) {
            return $this->auth->user();
        }

        return User::findOrFail($userId);
    }
}
