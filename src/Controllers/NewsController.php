<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Psr\Log\LoggerInterface;

class NewsController extends BaseController
{
    use HasUserNotifications;

    /** @var Authenticator */
    protected $auth;

    /** @var NewsComment */
    protected $comment;

    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $log;

    /** @var News */
    protected $news;

    /** @var Redirector */
    protected $redirect;

    /** @var Response */
    protected $response;

    /** @var Request */
    protected $request;

    /** @var array */
    protected $permissions = [
        'news',
        'meetings'      => 'user_meetings',
        'comment'       => 'news_comments',
        'deleteComment' => 'news_comments',
    ];

    /**
     * @param Authenticator   $auth
     * @param Config          $config
     * @param NewsComment     $comment
     * @param LoggerInterface $log
     * @param News            $news
     * @param Redirector      $redirector
     * @param Response        $response
     * @param Request         $request
     */
    public function __construct(
        Authenticator $auth,
        NewsComment $comment,
        Config $config,
        LoggerInterface $log,
        News $news,
        Redirector $redirector,
        Response $response,
        Request $request
    ) {
        $this->auth = $auth;
        $this->comment = $comment;
        $this->config = $config;
        $this->log = $log;
        $this->news = $news;
        $this->redirect = $redirector;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->showOverview();
    }

    /**
     * @return Response
     */
    public function meetings(): Response
    {
        return $this->showOverview(true);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $news = $this->news
            ->with('user')
            ->with('comments')
            ->findOrFail($request->getAttribute('id'));

        return $this->renderView('pages/news/news.twig', ['news' => $news]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function comment(Request $request): Response
    {
        $data = $this->validate($request, [
            'comment' => 'required',
        ]);
        $user = $this->auth->user();
        $news = $this->news
            ->findOrFail($request->getAttribute('id'));

        /** @var NewsComment $comment */
        $comment = $news->comments()->create([
            'text'    => $data['comment'],
            'user_id' => $user->id,
        ]);

        $this->log->info(
            'Created news comment for "{news}": {comment}',
            [
                'news'    => $news->title,
                'comment' => $comment->text,
            ]
        );

        $this->addNotification('news.comment.success');

        return $this->redirect->back();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteComment(Request $request): Response
    {
        $id = $request->getAttribute('id');
        $this->validate(
            $request,
            [
                'delete' => 'checked',
            ]
        );

        $comment = $this->comment->findOrFail($id);
        if (
            $comment->user->id != $this->auth->user()->id
            && !$this->auth->can('admin_news')
            && !$this->auth->can('comment.delete')
        ) {
            throw new HttpForbidden();
        }

        $comment->delete();

        $this->log->info(
            'Deleted comment "{comment}" of news "{news}"',
            ['comment' => $comment->text, 'news' => $comment->news->title]
        );
        $this->addNotification('news.comment-delete.success');

        return $this->redirect->to('/news/' . $comment->news->id);
    }

    /**
     * @param bool $onlyMeetings
     * @return Response
     */
    protected function showOverview(bool $onlyMeetings = false): Response
    {
        $query = $this->news;
        $page = $this->request->get('page', 1);
        $perPage = $this->config->get('display_news');

        if ($onlyMeetings) {
            $query = $query->where('is_meeting', true);
        }

        $news = $query
            ->with('user')
            ->withCount('comments')
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();
        $pagesCount = ceil($query->count() / $perPage);

        return $this->renderView(
            'pages/news/overview.twig',
            [
                'news'          => $news,
                'pages'         => max(1, $pagesCount),
                'page'          => max(1, min($page, $pagesCount)),
                'only_meetings' => $onlyMeetings,
                'is_overview'   => true,
            ]
        );
    }

    /**
     * @param string $page
     * @param array $data
     * @return Response
     */
    protected function renderView(string $page, array $data): Response
    {
        $data += $this->getNotifications();

        return $this->response->withView($page, $data);
    }
}
