<?php

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\LogEntry;

class LogsController extends BaseController
{
    protected LogEntry $log;

    protected Response $response;

    /** @var array */
    protected array $permissions = [
        'admin_log',
    ];

    public function __construct(LogEntry $log, Response $response)
    {
        $this->log = $log;
        $this->response = $response;
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
