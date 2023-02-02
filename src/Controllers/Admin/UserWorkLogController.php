<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Psr\Log\LoggerInterface;

class UserWorkLogController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_user_worklog',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Config $config,
        protected LoggerInterface $log,
        protected Worklog $worklog,
        protected Redirector $redirect,
        protected Response $response,
        protected User $user
    ) {
    }

    public function editWorklog(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $worklogId = $request->getAttribute('worklog_id'); // optional

        $user = $this->user->findOrFail($userId);

        if (isset($worklogId)) {
            $worklog = $this->worklog->findOrFail((int) $worklogId);

            if ($worklog->user->id != $userId) {
                throw new HttpNotFound();
            }
            return $this->showEditWorklog($user, $worklog->worked_at, $worklog->hours, $worklog->comment, true);
        } else {
            return $this->showEditWorklog($user, $this->getWorkDateSuggestion());
        }
    }

    public function saveWorklog(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $worklogId = $request->getAttribute('worklog_id'); // optional

        $user = $this->user->findOrFail($userId);

        $data = $this->validate($request, [
            'work_date'  => 'required|date:Y-m-d',
            'work_hours' => 'float|min:0',
            'comment'    => 'required|max:200',
        ]);

        if (isset($worklogId)) {
            $worklog = $this->worklog->findOrFail((int) $worklogId);

            if ($worklog->user->id != $userId) {
                throw new HttpNotFound();
            }
        } else {
            $worklog = new Worklog();
            $worklog->user()->associate($user);
            $worklog->creator()->associate($this->auth->user());
        }
        $worklog->worked_at = $data['work_date'];
        $worklog->hours = $data['work_hours'];
        $worklog->comment = $data['comment'];
        $worklog->save();

        $this->addNotification(isset($worklogId) ? 'worklog.edit.success' : 'worklog.add.success');

        return $this->redirect->to('/users?action=view&user_id=' . $userId);
        // TODO Once User_view.php gets removed, change this to withView + getNotifications
    }

    public function showDeleteWorklog(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $worklogId = (int) $request->getAttribute('worklog_id');

        $user = $this->user->findOrFail($userId);
        $worklog = $this->worklog->findOrFail($worklogId);

        if ($worklog->user->id != $userId) {
            throw new HttpNotFound();
        }

        return $this->response->withView(
            'admin/user/delete-worklog.twig',
            ['user' => $user]
        );
    }

    public function deleteWorklog(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $worklogId = (int) $request->getAttribute('worklog_id');

        $worklog = $this->worklog->findOrFail($worklogId);

        if ($worklog->user->id != $userId) {
            throw new HttpNotFound();
        }
        $worklog->delete();

        $this->addNotification('worklog.delete.success');

        return $this->redirect->to('/users?action=view&user_id=' . $userId);
        // TODO Once User_view.php gets removed, change this to withView + getNotifications
    }

    private function showEditWorklog(
        User $user,
        Carbon $work_date,
        float $work_hours = 0,
        string $comment = '',
        bool $is_edit = false
    ): Response {
        return $this->response->withView(
            'admin/user/edit-worklog.twig',
            [
                'user' => $user,
                'work_date' => $work_date,
                'work_hours' => $work_hours,
                'comment' => $comment,
                'is_edit' => $is_edit,
            ]
        );
    }

    private function getWorkDateSuggestion(): Carbon
    {
        $buildup_start = config('buildup_start');
        $event_start = config('event_start');

        $work_date_suggestion = Carbon::today();
        if (!empty($buildup_start) && (empty($event_start) || $event_start->lessThan(Carbon::now()))) {
            $work_date_suggestion = $buildup_start->startOfDay();
        }
        return $work_date_suggestion;
    }
}
