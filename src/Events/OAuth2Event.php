<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Illuminate\Support\Collection;

class OAuth2Event extends StoppableEvent
{
    public static string $LOGIN = 'oauth2.login';

    public function __construct(protected string $name, public string $providerName, public Collection $userdata)
    {
    }
}
