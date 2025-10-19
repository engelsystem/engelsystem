<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Validation\ValidatesRequest;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseController
{
    use ValidatesRequest;

    /** @var string[]|string[][] A list of Permissions required to access the controller or certain pages */
    protected array $permissions = [];

    /**
     * Returns the list of permissions for instance / methods
     *
     * @return string[]|string[][]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Check if the request should be permitted
     *
     * $this->getPermissions will be interpreted on null return
     */
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return null;
    }
}
