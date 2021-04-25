<?php

namespace Engelsystem\Events\Listener;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\News as NewsModel;
use Engelsystem\Models\User\Settings as UserSettings;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;
use Swift_SwiftException as SwiftException;

class News
{
    /** @var LoggerInterface */
    protected $log;

    /** @var EngelsystemMailer */
    protected $mailer;

    /** @var UserSettings */
    protected $settings;

    /**
     * @param LoggerInterface   $log
     * @param EngelsystemMailer $mailer
     * @param UserSettings      $settings
     */
    public function __construct(
        LoggerInterface $log,
        EngelsystemMailer $mailer,
        UserSettings $settings
    ) {
        $this->log = $log;
        $this->mailer = $mailer;
        $this->settings = $settings;
    }

    /**
     * @param NewsModel $news
     */
    public function created(NewsModel $news)
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

    /**
     * @param NewsModel $news
     * @param User      $user
     * @param string    $subject
     * @param string    $template
     */
    protected function sendMail(NewsModel $news, User $user, string $subject, string $template)
    {
        try {
            $this->mailer->sendViewTranslated(
                $user,
                $subject,
                $template,
                ['title' => $news->title, 'news' => $news, 'username' => $user->name]
            );
        } catch (SwiftException $e) {
            $this->log->error(
                'Unable to send email "{title}" to user {user} with {exception}',
                ['title' => $subject, 'user' => $user->name, 'exception' => $e]
            );
        }
    }
}
