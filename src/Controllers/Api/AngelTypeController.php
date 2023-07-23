<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypeController extends ApiController
{
    public function index(): Response
    {
        $news = AngelType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $data = ['data' => $news];
        return $this->response
            ->withContent(json_encode($data));
    }
}
