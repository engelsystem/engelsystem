<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api\Stub;

class UsesAuthWebPermissionImplementation extends UsesAuthImplementation
{
    protected function webPermission(string $method): string|array|bool|null
    {
        return match ($method) {
            'index' => 'web.read',
            'save' => 'web.write',
            'open' => true,
            default => null,
        };
    }
}
