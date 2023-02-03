<?php

declare(strict_types=1);

namespace Engelsystem\Mail;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Symfony\Component\Mailer\MailerInterface;

class EngelsystemMailer extends Mailer
{
    protected ?Renderer $view = null;

    protected ?Translator $translation = null;

    protected ?string $subjectPrefix = null;

    /**
     * @param Renderer|null   $view
     * @param Translator|null $translation
     */
    public function __construct(MailerInterface $mailer, Renderer $view = null, Translator $translation = null)
    {
        parent::__construct($mailer);

        $this->translation = $translation;
        $this->view = $view;
    }

    /**
     * @param string|string[]|User $to
     */
    public function sendViewTranslated(
        string|array|User $to,
        string $subject,
        string $template,
        array $data = [],
        ?string $locale = null
    ): void {
        if ($to instanceof User) {
            $locale = $locale ?: $to->settings->language;
            $to = $to->contact->email ?: $to->email;
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
        $this->sendView($to, $subject, $template, $data);

        if ($activeLocale) {
            $this->translation->setLocale($activeLocale);
        }
    }

    /**
     * Send a template
     *
     * @param string|string[] $to
     */
    public function sendView(string|array $to, string $subject, string $template, array $data = []): void
    {
        $body = $this->view->render($template, $data);

        $this->send($to, $subject, $body);
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     */
    public function send(string|array $to, string $subject, string $body): void
    {
        if ($this->subjectPrefix) {
            $subject = sprintf('[%s] %s', $this->subjectPrefix, trim($subject));
        }

        parent::send($to, $subject, $body);
    }

    public function getSubjectPrefix(): string
    {
        return $this->subjectPrefix;
    }

    public function setSubjectPrefix(string $subjectPrefix): void
    {
        $this->subjectPrefix = $subjectPrefix;
    }
}
