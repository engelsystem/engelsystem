<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Exceptions\HttpUnauthorized;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Allow authenticated users to access their own data on selected endpoints
 * without holding the full `api` privilege.
 *
 * Controllers using this trait declare the action methods that support
 * self-access via $ownRoutes. When the request targets the auth user
 * (user_id "self" or matching id) and the user has the `api.own` privilege,
 * permission is granted. Otherwise the trait returns null so the regular
 * permission pipeline applies.
 */
trait OwnAuth
{
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        $ownRoutes = property_exists($this, 'ownRoutes') ? $this->ownRoutes : [];
        if (!in_array($method, $ownRoutes, true)) {
            return null;
        }

        if (!isset($this->auth)) {
            return null;
        }

        $userId = $request->getAttribute('user_id');
        $user = $this->auth->user();

        if (!$user) {
            // Self routes called without auth should return 401, not 403
            // or, worse, 500 from dereferencing the missing user.
            if ($userId === 'self') {
                throw new HttpUnauthorized();
            }
            return null;
        }

        if (!$this->auth->can('api.own')) {
            return null;
        }

        if ($userId === 'self' || (int) $userId === $user->id) {
            return true;
        }

        return null;
    }
}
