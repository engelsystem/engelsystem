<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Psr\Log\LoggerInterface;

class NewsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_news',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected News $news,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function edit(Request $request): Response
    {
        $newsId = $request->getAttribute('news_id'); // optional

        $news = $this->news->find($newsId);
        $isMeeting = (bool) $request->get('meeting', false);

        return $this->showEdit($news, $isMeeting);
    }

    protected function showEdit(?News $news, bool $isMeetingDefault = false): Response
    {
        return $this->response->withView(
            'pages/news/edit.twig',
            [
                'news'           => $news,
                'is_meeting'     => $news ? $news->is_meeting : $isMeetingDefault,
                'is_pinned'      => $news ? $news->is_pinned : false,
                'is_highlighted' => $news ? $news->is_highlighted : false,
            ],
        );
    }

    public function save(Request $request): Response
    {
        $newsId = $request->getAttribute('news_id'); // optional

        /** @var News $news */
        $news = $this->news->findOrNew($newsId);

        if ($request->request->has('delete')) {
            $news->delete();

            $this->log->info(
                'Deleted {type} "{news}"',
                [
                    'type' => $news->is_meeting ? 'meeting' : 'news',
                    'news' => $news->title,
                ]
            );

            $this->addNotification('news.delete.success');

            return $this->redirect->to('/news');
        }

        $data = $this->validate($request, [
            'title'          => 'required',
            'text'           => 'required',
            'is_meeting'     => 'optional|checked',
            'is_pinned'      => 'optional|checked',
            'is_highlighted' => 'optional|checked',
            'delete'         => 'optional|checked',
            'preview'        => 'optional|checked',
        ]);

        if (!$news->user) {
            $news->user()->associate($this->auth->user());
        }
        $news->title = $data['title'];
        $news->text = $data['text'];
        $news->is_meeting = !is_null($data['is_meeting']);
        $news->is_pinned = !is_null($data['is_pinned']);

        if ($this->auth->can('news.highlight')) {
            $news->is_highlighted = !is_null($data['is_highlighted']);
        }

        if (!is_null($data['preview'])) {
            return $this->showEdit($news);
        }

        $isNewNews = !$news->id;
        if ($isNewNews && News::where('title', $news->title)->where('text', $news->text)->count()) {
            $this->addNotification('news.edit.duplicate', NotificationType::ERROR);
            return $this->showEdit($news);
        }
        $news->save();

        if ($isNewNews) {
            event('news.created', ['news' => $news]);
        }

        $this->log->info(
            'Updated {pinned}{type} "{news}": {text}',
            [
                'pinned'    => $news->is_pinned ? 'pinned ' : '',
                'highlighted' => $news->is_highlighted ? 'highlighted ' : '',
                'type'      => $news->is_meeting ? 'meeting' : 'news',
                'news'      => $news->title,
                'text'      => $news->text,
            ]
        );

        $this->addNotification('news.edit.success');

        return $this->redirect->to('/news');
    }
}
