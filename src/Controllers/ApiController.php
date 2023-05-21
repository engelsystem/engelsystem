<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Models\News;

class ApiController extends BaseController
{
    public array $permissions = [
        'api',
    ];

    public function __construct(protected Response $response)
    {
        $this->response = $this->response
            ->withHeader('content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }

    public function index(): Response
    {
        return $this->response
            ->withContent(json_encode([
                'versions' => [
                    '0.0.1-beta' => '/v0-beta',
                ],
            ]));
    }

    public function indexV0(): Response
    {
        return $this->response
            ->withContent(json_encode([
                'version' => '0.0.1-beta',
                'description' => 'Beta API, might break any second.',
                'paths' => [
                    '/news',
                ],
            ]));
    }

    public function options(): Response
    {
        return $this->response
            ->setStatusCode(204)
            ->withHeader('Allow', 'OPTIONS, HEAD, GET')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization');
    }

    public function notImplemented(): Response
    {
        return $this->response
            ->setStatusCode(501)
            ->withContent(json_encode(['error' => 'Not implemented']));
    }

    public function news(): Response
    {
        $news = News::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'text', 'is_meeting', 'is_pinned', 'is_highlighted', 'created_at', 'updated_at']);

        return $this->response
            ->withContent(json_encode($news));
    }
}
