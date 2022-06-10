<?php

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Psr\Log\LoggerInterface;

class NewsController extends BaseController
{
    use HasUserNotifications;

    /** @var Authenticator */
    protected $auth;

    /** @var LoggerInterface */
    protected $log;

    /** @var News */
    protected $news;

    /** @var Redirector */
    protected $redirect;

    /** @var Response */
    protected $response;

    /** @var array */
    protected $permissions = [
        'admin_news',
    ];

    /**
     * @param Authenticator   $auth
     * @param LoggerInterface $log
     * @param News            $news
     * @param Redirector      $redirector
     * @param Response        $response
     */
    public function __construct(
        Authenticator $auth,
        LoggerInterface $log,
        News $news,
        Redirector $redirector,
        Response $response
    ) {
        $this->auth = $auth;
        $this->log = $log;
        $this->news = $news;
        $this->redirect = $redirector;
        $this->response = $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request): Response
    {
        $id = $request->getAttribute('id');
        $news = $this->news->find($id);
        $isMeeting = $request->get('meeting', false);

        return $this->showEdit($news, $isMeeting);
    }

    /**
     * @param News|null $news
     * @param bool      $isMeetingDefault
     *
     * @return Response
     */
    protected function showEdit(?News $news, bool $isMeetingDefault = false): Response
    {
        return $this->response->withView(
            'pages/news/edit.twig',
            [
                'news'       => $news,
                'is_meeting' => $news ? $news->is_meeting : $isMeetingDefault,
                'is_pinned'  => $news ? $news->is_pinned : false,
            ] + $this->getNotifications(),
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function save(Request $request): Response
    {
        $id = $request->getAttribute('id');
        /** @var News $news */
        $news = $this->news->findOrNew($id);

        $data = $this->validate($request, [
            'title'      => 'required',
            'text'       => 'required',
            'is_meeting' => 'optional|checked',
            'is_pinned'  => 'optional|checked',
            'delete'     => 'optional|checked',
            'preview'    => 'optional|checked',
        ]);

        if (!is_null($data['delete'])) {
            $news->delete();

            $this->log->info(
                'Deleted {type} "{news}"',
                [
                    'type' => $news->is_meeting ? 'meeting' : 'news',
                    'news' => $news->title
                ]
            );

            $this->addNotification('news.delete.success');

            return $this->redirect->to('/news');
        }

        if (!$news->user) {
            $news->user()->associate($this->auth->user());
        }
        $news->title = $data['title'];
        $news->text = $data['text'];
        $news->is_meeting = !is_null($data['is_meeting']);
        $news->is_pinned = !is_null($data['is_pinned']);

        if (!is_null($data['preview'])) {
            return $this->showEdit($news);
        }

        $isNewNews = !$news->id;
        $news->save();

        if ($isNewNews) {
            event('news.created', ['news' => $news]);
        }

        $this->log->info(
            'Updated {pinned}{type} "{news}": {text}',
            [
                'pinned' => $news->is_pinned ? 'pinned ' : '',
                'type'   => $news->is_meeting ? 'meeting' : 'news',
                'news'   => $news->title,
                'text'   => $news->text,
            ]
        );

        $this->addNotification('news.edit.success');

        return $this->redirect->to('/news');
    }
}
