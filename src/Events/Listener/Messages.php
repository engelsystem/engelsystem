<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class Messages
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer
    ) {
    }

    public function created(Message $message): void
    {
        if (!$message->receiver->settings->email_messages) {
            return;
        }

        $this->sendMail($message, $message->receiver, 'notification.messages.new', 'emails/messages-new');
    }

    private function sendMail(Message $message, User $user, string $subject, string $template): void
    {
        $this->mailer->sendViewTranslated(
            $user,
            $subject,
            $template,
            [
                'sender'       => $message->sender->displayName,
                'send_message' => $message,
                'username'     => $user->displayName,
            ]
        );
    }
}
