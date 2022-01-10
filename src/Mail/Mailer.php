<?php

namespace Engelsystem\Mail;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    /** @var MailerInterface */
    protected $mailer;

    /** @var string */
    protected $fromAddress = '';

    /** @var string */
    protected $fromName = null;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     * @param string          $subject
     * @param string          $body
     */
    public function send($to, string $subject, string $body): void
    {
        $message = (new Email())
            ->to(...(array)$to)
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->subject($subject)
            ->text($body);

        $this->mailer->send($message);
    }

    /**
     * @return string
     */
    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    /**
     * @param string $fromAddress
     */
    public function setFromAddress(string $fromAddress)
    {
        $this->fromAddress = $fromAddress;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName(string $fromName)
    {
        $this->fromName = $fromName;
    }
}
