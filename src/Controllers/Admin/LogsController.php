<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;

class LogsController extends BaseController
{
    /** @var array<string> */
    protected array $permissions = [
        'admin_log',
    ];

    public function __construct(protected LogEntry $log, protected Response $response, protected Authenticator $auth)
    {
    }

    public function index(Request $request): Response
    {
        $searchUserId = (int) $request->input('search_user_id') ?: null;
        $search = $request->input('search');
        $userId = $this->auth->user()?->id;

        if ($this->auth->can('logs.all')) {
            $userId = $searchUserId;
        }

        $entries = $this->log->filter($search, $userId);

        /** @var Collection $users */
        $users = User::with('personalData')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (User $u) {
                return [$u->id => $u->displayName];
            });

        return $this->response->withView(
            'admin/log.twig',
            ['entries' => $entries, 'search' => $search, 'users' => $users, 'search_user_id' => $searchUserId]
        );
    }
}
