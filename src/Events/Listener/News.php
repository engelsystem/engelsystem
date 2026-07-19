<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\News as NewsModel;
use Engelsystem\Models\NewsComment;
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

    public function created(NewsModel $news, bool $sendNotification = true): void
    {
        $this->sendMail($news, 'notification.news.new', 'emails/news-new', $sendNotification);
    }

    public function updated(NewsModel $news, bool $sendNotification = true): void
    {
        $this->sendMail($news, 'notification.news.updated', 'emails/news-updated', $sendNotification);
    }

    public function commentCreated(NewsComment $comment): void
    {
        $news = $comment->news;
        $author = $news->user;

        // Don't notify if the author commented on their own news
        if ($comment->user_id === $author->id) {
            return;
        }

        // Don't notify if author has email_news disabled
        if (!$author->settings->email_news) {
            return;
        }

        $this->mailer->sendViewTranslated(
            $author,
            'notification.news.comment.new',
            'emails/news-comment-new',
            [
                'news' => $news,
                'comment' => $comment,
                'username' => $author->displayName,
            ]
        );
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
