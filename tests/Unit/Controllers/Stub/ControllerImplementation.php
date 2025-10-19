<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\BaseController;
use Psr\Http\Message\ServerRequestInterface;

class ControllerImplementation extends BaseController
{
    /** @var string[]|string[][] */
    protected array $permissions = [
        'foo',
        'lorem' => [
            'ipsum',
            'dolor',
        ],
    ];

    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return match ($method) {
            'yay' => true,
            'nope' => false,
            default => parent::hasPermission($request, $method),
        };
    }
}
