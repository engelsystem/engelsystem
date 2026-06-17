<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;
use Random\RandomException;

class Password
{
    /**
     * Trigger a password reset for the given user. This will email the user!
     *
     * @param User $user The user to trigger the password reset for.
     * @throws RandomException
     */
    public static function triggerPasswordReset(User $user): void
    {
        /** @var EngelsystemMailer $mailer */
        $mailer = app(EngelsystemMailer::class);
        /** @var LoggerInterface $logger */
        $logger = app(LoggerInterface::class);

        $reset = (new \Engelsystem\Models\User\PasswordReset())->findOrNew($user->id);
        $reset->user_id = $user->id;
        $reset->token = bin2hex(random_bytes(16));
        $reset->save();

        $logger->info(
            sprintf('Password recovery for %s (%u)', $user->name, $user->id),
            ['user' => $user->toJson()]
        );

        $mailer->sendViewTranslated(
            $user,
            'Password recovery',
            'emails/password-reset',
            ['username' => $user->displayName, 'reset' => $reset]
        );
    }
}
