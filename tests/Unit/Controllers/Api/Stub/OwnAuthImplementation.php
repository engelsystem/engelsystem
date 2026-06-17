<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api\Stub;

use Engelsystem\Controllers\Api\ApiController;
use Engelsystem\Controllers\Api\OwnAuth;
use Engelsystem\Controllers\Api\UsesAuth;

class OwnAuthImplementation extends ApiController
{
    use OwnAuth;
    use UsesAuth;

    /** @var string[] */
    protected array $ownRoutes = ['allowed'];

    public function setOwnRoutes(array $routes): void
    {
        $this->ownRoutes = $routes;
    }
}
