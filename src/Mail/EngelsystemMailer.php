<?php

namespace Engelsystem\Mail;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Swift_Mailer as SwiftMailer;

class EngelsystemMailer extends Mailer
{
    /** @var Renderer|null */
    protected $view;

    /** @var Translator|null */
    protected $translation;

    /** @var string */
    protected $subjectPrefix = null;

    /**
     * @param SwiftMailer $mailer
     * @param Renderer    $view
     * @param Translator  $translation
     */
    public function __construct(SwiftMailer $mailer, Renderer $view = null, Translator $translation = null)
    {
        parent::__construct($mailer);

        $this->translation = $translation;
        $this->view = $view;
    }

    /**
     * @param string|string[]|User $to
     * @param string               $subject
     * @param string               $template
     * @param array                $data
     * @param string|null          $locale
     * @return int
     */
    public function sendViewTranslated(
        $to,
        string $subject,
        string $template,
        array $data = [],
        ?string $locale = null
    ): int {
        if ($to instanceof User) {
            $locale = $locale ?: $to->settings->language;
            $to = $to->contact->email ? $to->contact->email : $to->email;
        }

        $activeLocale = null;
        if (
            $locale
            && $this->translation
            && isset($this->translation->getLocales()[$locale])
        ) {
            $activeLocale = $this->translation->getLocale();
            $this->translation->setLocale($locale);
        }

        $subject = $this->translation ? $this->translation->translate($subject, $data) : $subject;
        $sentMails = $this->sendView($to, $subject, $template, $data);

        if ($activeLocale) {
            $this->translation->setLocale($activeLocale);
        }

        return $sentMails;
    }

    /**
     * Send a template
     *
     * @param string|string[] $to
     * @param string          $subject
     * @param string          $template
     * @param array           $data
     * @return int
     */
    public function sendView($to, string $subject, string $template, array $data = []): int
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
