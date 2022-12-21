<?php

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\LogEntry;

class LogsController extends BaseController
{
    /** @var array<string> */
    protected array $permissions = [
        'admin_log',
    ];

    public function __construct(protected LogEntry $log, protected Response $response)
    {
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $entries = $this->log->filter($search);

        return $this->response->withView(
            'admin/log.twig',
            ['entries' => $entries, 'search' => $search]
        );
    }
}
