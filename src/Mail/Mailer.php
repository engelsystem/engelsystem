<?php

namespace Engelsystem\Mail;

use Engelsystem\Renderer\Renderer;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;

class Mailer
{
    /** @var SwiftMailer */
    protected $mailer;

    /** @var Renderer|null */
    protected $view;

    /** @var string */
    protected $fromAddress = '';

    /** @var string */
    protected $fromName = null;

    public function __construct(SwiftMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     * @param string          $subject
     * @param string          $body
     * @return int
     */
    public function send($to, string $subject, string $body): int
    {
        /** @var SwiftMessage $message */
        $message = $this->mailer->createMessage();
        $message->setTo((array)$to)
            ->setFrom($this->fromAddress, $this->fromName)
            ->setSubject($subject)
            ->setBody($body);

        return $this->mailer->send($message);
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
