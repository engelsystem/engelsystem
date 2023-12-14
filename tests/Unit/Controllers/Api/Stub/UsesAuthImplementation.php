<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api\Stub;

use Engelsystem\Controllers\Api\ApiController;
use Engelsystem\Controllers\Api\UsesAuth;
use Engelsystem\Models\User\User;

class UsesAuthImplementation extends ApiController
{
    use UsesAuth;

    public function user(string|int $id): ?User
    {
        return $this->getUser($id);
    }
}
