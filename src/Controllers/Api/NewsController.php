<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\NewsDetailResource;
use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;

class NewsController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the "news" privilege, same as the news page on the website
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return 'news';
    }

    public function index(): Response
    {
        $models = News::query()
            ->withCount('comments')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get();

        $data = ['data' => NewsResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('news_id');
        /** @var News $news */
        $news = News::query()
            ->withCount('comments')
            ->with('comments.user')
            ->findOrFail($id);

        $data = ['data' => (new NewsDetailResource($news))->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }
}
