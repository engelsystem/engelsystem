<?php

declare(strict_types=1);

namespace Engelsystem\Mail;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    protected string $fromAddress = '';

    protected ?string $fromName = null;

    public function __construct(protected MailerInterface $mailer)
    {
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     */
    public function send(string|array $to, string $subject, string $body): void
    {
        $message = (new Email())
            ->to(...(array) $to)
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->subject($subject)
            ->text($body);

        $this->mailer->send($message);
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
