<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Events\DataEvent;
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

    public function created(DataEvent $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $this->mailer->sendViewTranslated(
            $user,
            'email.user.welcome.subject',
            'emails/user-welcome',
            ['app_name' => config('app_name'), 'username' => $user->displayName]
        );
    }
}
