<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\News as NewsModel;
use Engelsystem\Models\User\Settings as UserSettings;
use Engelsystem\Models\User\User;
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

    public function created(NewsModel $news): void
    {
        /** @var UserSettings[]|Collection $recipients */
        $recipients = $this->settings
            ->whereEmailNews(true)
            ->with('user')
            ->get();

        foreach ($recipients as $recipient) {
            $this->sendMail($news, $recipient->user, 'notification.news.new', 'emails/news-new');
        }
    }

    protected function sendMail(NewsModel $news, User $user, string $subject, string $template): void
    {
        $this->mailer->sendViewTranslated(
            $user,
            $subject,
            $template,
            ['title' => $news->title, 'news' => $news, 'username' => $user->displayName]
        );
    }
}
