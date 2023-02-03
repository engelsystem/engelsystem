<?php

declare(strict_types=1);

namespace Engelsystem\Logger;

use Engelsystem\Helpers\Authenticator;
use Psr\Log\InvalidArgumentException;
use Stringable;

class UserAwareLogger extends Logger
{
    protected Authenticator $auth;

    /**
     * Logs with an arbitrary level and prepends the user
     * @throws InvalidArgumentException
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        if ($this->auth && ($user = $this->auth->user())) {
            $message = sprintf('%s (%u): %s', $user->name, $user->id, $message);
        }

        parent::log($level, $message, $context);
    }

    public function setAuth(Authenticator $auth): void
    {
        $this->auth = $auth;
    }
}
