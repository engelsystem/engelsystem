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
     * @return Response
     */
    public function edit(Request $request): Response
    {
        $id = $request->getAttribute('id');
        $news = $this->news->find($id);

        if (
            $news
            && !$this->auth->can('admin_news_html')
            && strip_tags($news->text) != $news->text
        ) {
            $this->addNotification('news.edit.contains-html', 'warnings');
        }

        return $this->response->withView(
            'pages/news/edit.twig',
            ['news' => $news] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
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
            'delete'     => 'optional|checked',
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

        if (!$this->auth->can('admin_news_html')) {
            $data['text'] = strip_tags($data['text']);
        }

        if (!$news->user) {
            $news->user()->associate($this->auth->user());
        }
        $news->title = $data['title'];
        $news->text = $data['text'];
        $news->is_meeting = !is_null($data['is_meeting']);
        $news->save();

        $this->log->info(
            'Updated {type} "{news}": {text}',
            [
                'type' => $news->is_meeting ? 'meeting' : 'news',
                'news' => $news->title,
                'text' => $news->text,
            ]
        );

        $this->addNotification('news.edit.success');

        return $this->redirect->to('/news/' . $news->id);
    }
}
