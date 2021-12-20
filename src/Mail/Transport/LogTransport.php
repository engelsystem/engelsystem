<?php

namespace Engelsystem\Mail\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class LogTransport extends AbstractTransport
{
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * Send the message to log
     *
     * @param SentMessage $message
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'log://';
    }
}
