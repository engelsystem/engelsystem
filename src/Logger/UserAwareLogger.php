<?php

declare(strict_types=1);

namespace Engelsystem\Logger;

use Engelsystem\Helpers\Authenticator;

class UserAwareLogger extends Logger
{
    protected ?Authenticator $auth;

    /**
     * Adds the authenticated user to the log message
     */
    public function createEntry(array $data): void
    {
        if ($this->auth && ($user = $this->auth->user())) {
            $data['user_id'] = $user->id;
        }

        parent::createEntry($data);
    }

    public function setAuth(Authenticator $auth): void
    {
        $this->auth = $auth;
    }
}
