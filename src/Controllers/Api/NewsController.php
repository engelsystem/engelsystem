<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Psr\Http\Message\ServerRequestInterface;

class NewsController extends ApiController
{
    use UsesAuth;

    /**
     * News don't require the "api" privilege, unlike the rest of the API - any
     * authenticated user may read them, same as on the website (the "news" privilege).
     */
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return (bool) $this->auth?->user();
    }

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
