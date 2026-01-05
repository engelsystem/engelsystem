<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Entity;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use EntityTrait;
    use RefreshTokenTrait;
}
