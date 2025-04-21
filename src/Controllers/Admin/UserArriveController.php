<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class UserArriveController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_arrive',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected Redirector $redirect,
        protected Response $response,
        protected User $user
    ) {
    }


    public function saveArrive(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        /** @var User $user */

        $user = $this->user->findOrFail($userId);
        $data = $this->validate($request, [
            'action' => 'in:reset,arrive',
        ]);
        $action = $data['action'];

        if ($action == 'arrive') {
            $user->state->arrived = true;
            $user->state->arrival_date = new Carbon();
        } else {
            $user->state->arrived = false;
            $user->state->arrival_date = null;
        }

        $user->state->save();

        $this->log->info(
            '{name} ({id}) {action}',
            [
                'name' => $user->name,
                'id' => $user->id,
                'action' => $action == 'arrive' ? 'has arrived' : 'has disappeared (arrive reset)',
            ]
        );

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            // This was an async request, send a JSON response.
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withContent(json_encode([
                    'state' => $user->state->arrived,
                ]));
        }

        $this->addNotification($action == 'arrive'
            ? __('Angel has been marked as arrived.')
            : __('Reset done. Angel has not arrived.'));

        return $this->redirect->to('/users?action=view&user_id=' . $user->id);
        // TODO Once User_view.php gets removed, change this to withView + getNotifications
    }
}
