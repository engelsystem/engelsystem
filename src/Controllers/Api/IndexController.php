<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;

class IndexController extends ApiController
{
    public array $permissions = [
        'index' => 'api',
        'indexV0' => 'api',
    ];

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
        // Respond to browser preflight options requests
        return $this->response
            ->setStatusCode(204)
            ->withHeader('allow', 'OPTIONS, HEAD, GET')
            ->withHeader('access-control-allow-headers', 'Authorization');
    }

    public function notImplemented(): Response
    {
        return $this->response
            ->setStatusCode(501)
            ->withContent(json_encode(['error' => 'Not implemented']));
    }
}
