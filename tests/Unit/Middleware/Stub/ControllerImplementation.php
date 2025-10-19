<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Engelsystem\Controllers\BaseController;
use Psr\Http\Message\ServerRequestInterface;

class ControllerImplementation extends BaseController
{
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function actionStub(): string
    {
        return '';
    }

    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return match ($method) {
            'allow' => true,
            'deny' => false,
            default => parent::hasPermission($request, $method),
        };
    }

    public function allow(): string
    {
        return 'yay';
    }

    public function deny(): string
    {
        return 'nope';
    }
}
