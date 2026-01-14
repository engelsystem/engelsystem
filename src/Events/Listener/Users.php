<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class Users
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer
    ) {
    }

    public function created(User $user): void
    {
        $this->mailer->sendViewTranslated(
            $user,
            'email.user.welcome.subject',
            'emails/user-welcome',
            ['username' => $user->displayName]
        );
    }
}
