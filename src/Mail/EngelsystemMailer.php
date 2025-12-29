<?php

declare(strict_types=1);

namespace Engelsystem\Mail;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class EngelsystemMailer extends Mailer
{
    protected ?string $subjectPrefix = null;

    public function __construct(
        LoggerInterface $log,
        MailerInterface $mailer,
        protected ?Renderer $view = null,
        protected ?Translator $translation = null
    ) {
        parent::__construct($log, $mailer);
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
    ): bool {
        if ($to instanceof User) {
            $locale = $locale ?: $to->settings->language;
            $to = $to->contact->email ?: $to->email;
        }

        $activeLocale = null;
        if (
            $locale
            && $this->translation
            && in_array($locale, $this->translation->getLocales())
        ) {
            $activeLocale = $this->translation->getLocale();
            $this->translation->setLocale($locale);
        }

        $subject = $this->translation ? $this->translation->translate($subject, $data) : $subject;
        $status = $this->sendView($to, $subject, $template, $data);

        if ($activeLocale) {
            $this->translation->setLocale($activeLocale);
        }

        return $status;
    }

    /**
     * Send a template
     *
     * @param string|string[] $to
     */
    public function sendView(string|array $to, string $subject, string $template, array $data = []): bool
    {
        $body = $this->view->render($template, $data);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send the mail
     *
     * @param string|string[] $to
     */
    public function send(string|array $to, string $subject, string $body): bool
    {
        if ($this->subjectPrefix) {
            $subject = sprintf('[%s] %s', $this->subjectPrefix, trim($subject));
        }

        return parent::send($to, $subject, $body);
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
