<?php

namespace Engelsystem\Controllers\idp;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;


class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait;
}
