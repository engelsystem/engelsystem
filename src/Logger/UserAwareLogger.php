<?php

namespace Engelsystem\Logger;

use Engelsystem\Helpers\Authenticator;
use Psr\Log\InvalidArgumentException;

class UserAwareLogger extends Logger
{
    /** @var Authenticator */
    protected $auth;

    /**
     * Logs with an arbitrary level and prepends the user
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        if ($this->auth && ($user = $this->auth->user())) {
            $message = sprintf('%s (%u): %s', $user->name, $user->id, $message);
        }

        parent::log($level, $message, $context);
    }

    /**
     * @param Authenticator $auth
     */
    public function setAuth(Authenticator $auth): void
    {
        $this->auth = $auth;
    }
}
