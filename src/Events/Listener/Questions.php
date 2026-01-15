<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class Questions
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer
    ) {
    }

    public function created(Question $question): void
    {
        // Notify all users who can answer questions (have question.edit permission)
        $recipients = User::whereHas('groups.privileges', function ($query): void {
            $query->where('name', 'question.edit');
        })->with('personalData', 'settings')->get();

        foreach ($recipients as $recipient) {
            // Don't notify the user who asked the question
            if ($recipient->id === $question->user_id) {
                continue;
            }

            $this->mailer->sendViewTranslated(
                $recipient,
                'notification.question.new',
                'emails/question-new',
                [
                    'question' => $question,
                    'username' => $recipient->displayName,
                ]
            );
        }
    }
}
