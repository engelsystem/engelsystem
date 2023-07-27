<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use Engelsystem\Models\News;

class NewsController extends ApiController
{
    public function index(): Response
    {
        $models = News::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'text', 'is_meeting', 'is_pinned', 'is_highlighted', 'created_at', 'updated_at']);

        $models->map(function (News $model): void {
            $model->url = $this->url->to('/news/' . $model->id);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }
}
