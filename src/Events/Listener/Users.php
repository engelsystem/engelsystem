<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Helpers\Confirmation;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class Users
{
    public function __construct(
        protected Confirmation $confirm,
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer,
    ) {
    }

    public function created(User $user): void
    {
        $confirmationUrl = null;
        if ($this->confirm->confirmationAvailable()) {
            $confirmationUrl = $this->confirm->getEmailConfirmationLink($user);
        }

        $this->mailer->sendViewTranslated(
            $user,
            'email.user.welcome.subject',
            'emails/user-welcome',
            [
                'app_name' => config('app_name'),
                'username' => $user->displayName,
                'confirmation_url' => $confirmationUrl,
            ],
        );
    }
}
