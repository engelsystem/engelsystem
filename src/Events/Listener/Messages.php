<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Events\ModelEvent;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;

class Messages
{
    public function __construct(
        protected EngelsystemMailer $mailer
    ) {
    }

    public function created(ModelEvent $event): void
    {
        /** @var Message $message */
        $message = $event->model;
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
