<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Events\NewsEvent;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\News as NewsModel;
use Engelsystem\Models\User\Settings as UserSettings;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class News
{
    public function __construct(
        protected LoggerInterface $log,
        protected EngelsystemMailer $mailer,
        protected UserSettings $settings
    ) {
    }

    public function created(NewsEvent $event): void
    {
        $this->sendMail($event->news, 'notification.news.new', 'emails/news-new', $event->sendNotification);
    }

    public function updated(NewsEvent $event): void
    {
        $this->sendMail($event->news, 'notification.news.updated', 'emails/news-updated', $event->sendNotification);
    }

    protected function sendMail(NewsModel $news, string $subject, string $template, bool $sendNotification = true): void
    {
        if (!$sendNotification) {
            return;
        }

        /** @var UserSettings[]|Collection $recipients */
        $recipients = $this->settings
            ->with('user.personalData')
            ->where('email_news', true)
            ->get();

        foreach ($recipients as $recipient) {
            $this->mailer->sendViewTranslated(
                $recipient->user,
                $subject,
                $template,
                ['title' => $news->title, 'news' => $news, 'username' => $recipient->user->displayName]
            );
        }
    }
}
