<?php

declare(strict_types=1);

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

    /** @var array<string, string> */
    protected array $permissions = [
        'news',
        'meetings'      => 'user_meetings',
        'comment'       => 'news_comments',
        'deleteComment' => 'news_comments',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected NewsComment $comment,
        protected Config $config,
        protected LoggerInterface $log,
        protected News $news,
        protected Redirector $redirect,
        protected Response $response,
        protected Request $request
    ) {
    }

    public function index(): Response
    {
        return $this->showOverview();
    }

    public function meetings(): Response
    {
        return $this->showOverview(true);
    }

    public function show(Request $request): Response
    {
        $newsId = (int) $request->getAttribute('news_id');

        $news = $this->news
            ->with('user')
            ->with('comments')
            ->findOrFail($newsId);

        return $this->renderView('pages/news/news.twig', ['news' => $news]);
    }

    public function comment(Request $request): Response
    {
        $newsId = (int) $request->getAttribute('news_id');

        $data = $this->validate($request, [
            'comment' => 'required',
        ]);
        $user = $this->auth->user();
        $news = $this->news->findOrFail($newsId);

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

    public function deleteComment(Request $request): Response
    {
        $commentId = (int) $request->getAttribute('comment_id');

        $this->validate(
            $request,
            [
                'delete' => 'checked',
            ]
        );

        $comment = $this->comment->findOrFail($commentId);
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
            ->orderByDesc('is_important')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
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
     * @param array $data
     */
    protected function renderView(string $page, array $data): Response
    {
        return $this->response->withView($page, $data);
    }
}
