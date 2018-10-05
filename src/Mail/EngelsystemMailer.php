<?php

namespace Engelsystem\Mail;

use Engelsystem\Renderer\Renderer;
use Swift_Mailer as SwiftMailer;

class EngelsystemMailer extends Mailer
{
    /** @var Renderer|null */
    protected $view;

    /** @var string */
    protected $subjectPrefix = null;

    /**
     * @param SwiftMailer $mailer
     * @param Renderer    $view
     */
    public function __construct(SwiftMailer $mailer, Renderer $view = null)
    {
        parent::__construct($mailer);

        $this->view = $view;
    }

    /**
     * Send a template
     *
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array  $data
     * @return int
     */
    public function sendView($to, $subject, $template, $data = []): int
    {
        $body = $this->view->render($template, $data);

        return $this->send($to, $subject, $body);
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
        if ($this->subjectPrefix) {
            $subject = sprintf('[%s] %s', $this->subjectPrefix, $subject);
        }

        return parent::send($to, $subject, $body);
    }

    /**
     * @return string
     */
    public function getSubjectPrefix(): string
    {
        return $this->subjectPrefix;
    }

    /**
     * @param string $subjectPrefix
     */
    public function setSubjectPrefix(string $subjectPrefix)
    {
        $this->subjectPrefix = $subjectPrefix;
    }
}
