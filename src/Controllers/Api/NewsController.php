<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;

class NewsController extends ApiController
{
    public function index(): Response
    {
        $models = News::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get();

        $data = ['data' => NewsResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }
}
