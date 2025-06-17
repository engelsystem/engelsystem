<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Helpers\Authenticator;

class AccessibleAuthenticator extends Authenticator
{
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}
