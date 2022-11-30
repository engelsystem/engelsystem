<?php

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

    /** @var Authenticator */
    protected $auth;

    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $log;

    /** @var Worklog */
    protected $worklog;

    /** @var Redirector */
    protected $redirect;

    /** @var Response */
    protected $response;

    /** @var User */
    protected $user;

    /** @var array */
    protected $permissions = [
        'admin_user_worklog',
    ];

    /**
     * @param Authenticator   $auth
     * @param Config          $config
     * @param LoggerInterface $log
     * @param Worklog         $worklog
     * @param Redirector      $redirector
     * @param Response        $response
     * @param User            $user
     */
    public function __construct(
        Authenticator $auth,
        Config $config,
        LoggerInterface $log,
        Worklog $worklog,
        Redirector $redirector,
        Response $response,
        User $user
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->log = $log;
        $this->worklog = $worklog;
        $this->redirect = $redirector;
        $this->response = $response;
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function editWorklog(Request $request): Response
    {
        $user_id = $request->getAttribute('id');
        $user = $this->user->findOrFail($user_id);

        $worklog_id = $request->getAttribute('worklog_id');
        if (isset($worklog_id)) {
            $worklog = $this->worklog->findOrFail($worklog_id);

            if ($worklog->user->id != $user_id) {
                throw new HttpNotFound();
            }
            return $this->showEditWorklog($user, $worklog->worked_at, $worklog->hours, $worklog->comment, true);
        } else {
            return $this->showEditWorklog($user, $this->getWorkDateSuggestion());
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function saveWorklog(Request $request): Response
    {
        $user_id = $request->getAttribute('id');
        $user = $this->user->findOrFail($user_id);

        $data = $this->validate($request, [
            'work_date'  => 'required|date:Y-m-d',
            'work_hours' => 'float|min:0',
            'comment'    => 'required|max:200',
        ]);

        $worklog_id = $request->getAttribute('worklog_id');
        if (isset($worklog_id)) {
            $worklog = $this->worklog->findOrFail($worklog_id);

            if ($worklog->user->id != $user_id) {
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

        $this->addNotification(isset($worklog_id) ? 'worklog.edit.success' : 'worklog.add.success');

        return $this->redirect->back();
    }

    private function showEditWorklog($user, $work_date, $work_hours = 0, $comment = '', $is_edit = false): Response
    {
        return $this->response->withView(
            'admin/user/edit-worklog.twig',
            [
                'user' => $user,
                'work_date' => $work_date,
                'work_hours' => $work_hours,
                'comment' => $comment,
                'is_edit' => $is_edit,
            ] + $this->getNotifications()
        );
    }

    /**
     * @return Carbon
     */
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
