<?php

namespace Engelsystem\Mail;

use Engelsystem\Renderer\Renderer;
use Swift_Mailer as SwiftMailer;

class EngelsystemMailer extends Mailer
{
    /** @var Renderer|null */
    protected $view;

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
}
