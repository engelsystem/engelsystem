<?php

declare(strict_types=1);

namespace Engelsystem\Mail\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class LogTransport extends AbstractTransport
{
    public function __construct(protected LoggerInterface $logger)
    {

        parent::__construct();
    }

    /**
     * Send the message to log
     */
    protected function doSend(SentMessage $message): void
    {
        $recipients = [];
        $messageRecipients = $message->getEnvelope()->getRecipients();
        foreach ($messageRecipients as $recipient) {
            $recipients[] = $recipient->toString();
        }

        $this->logger->debug(
            'Mail: Send mail to "{recipients}":' . PHP_EOL . PHP_EOL . '{content}',
            [
                'recipients' => implode(', ', $recipients),
                'content'    => $message->toString(),
            ]
        );
    }

    public function __toString(): string
    {
        return 'log://';
    }
}
