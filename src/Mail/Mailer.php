<?php

declare(strict_types=1);

namespace Engelsystem\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;

class Mailer
{
    protected string $fromAddress = '';

    protected ?string $fromName = null;

    public function __construct(protected LoggerInterface $log, protected MailerInterface $mailer)
    {
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     */
    public function send(string|array $to, string $subject, string $body): bool
    {
        $message = (new Email())
            ->to(...(array) $to)
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->subject($subject)
            ->text($body);

        try {
            $this->mailer->send($message);
        } catch (Throwable $e) {
            $this->log->error(
                'Unable to send e-mail "{subject}" to {to} in {file}:{line}: {type}: {message}',
                [
                    'subject' => $subject,
                    'to' => $to,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            return false;
        }

        return true;
    }

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function setFromAddress(string $fromAddress): void
    {
        $this->fromAddress = $fromAddress;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }
}
