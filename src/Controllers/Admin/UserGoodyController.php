<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Config\GoodyType;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class UserGoodyController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string, string> */
    protected array $permissions = [
        'editGoody' => 'user.goody.edit',
        'saveGoody' => 'user.goody.edit',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Config $config,
        protected LoggerInterface $log,
        protected Redirector $redirect,
        protected Response $response,
        protected User $user
    ) {
    }

    public function editGoody(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');

        $user = $this->user->findOrFail($userId);

        return $this->response->withView(
            'admin/user/edit-goody.twig',
            [
                'userdata' => $user,
                'is_tshirt' => $this->config->get('goody_type') === GoodyType::Tshirt->value,
            ]
        );
    }

    public function saveGoody(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $shirtEnabled = $this->config->get('goody_type') === GoodyType::Tshirt->value;
        /** @var User $user */
        $user = $this->user->findOrFail($userId);

        $data = $this->validate($request, [
            'shirt_size' => ($shirtEnabled ? 'required' : 'optional') . '|shirt_size',
            'arrived'    => 'optional|checked',
            'active'     => 'optional|checked',
            'got_goody'  => 'optional|checked',
        ]);

        if ($shirtEnabled) {
            $user->personalData->shirt_size = $data['shirt_size'];
            $user->personalData->save();
        }

        if ($this->auth->can('admin_arrive')) {
            $user->state->arrived = (bool) $data['arrived'];
        }

        $user->state->active = (bool) $data['active'];
        $user->state->got_goody = (bool) $data['got_goody'];
        $user->state->save();

        $this->log->info(
            'Updated user shirt state "{user}" ({id}): '
            . '{size}, arrived: {arrived}, active: {active}, got shirt: {got_goody}',
            [
                'id'        => $user->id,
                'user'      => $user->name,
                'size'      => $user->personalData->shirt_size,
                'arrived'   => $user->state->arrived ? 'yes' : 'no',
                'active'    => $user->state->active ? 'yes' : 'no',
                'got_goody' => $user->state->got_goody ? 'yes' : 'no',
            ]
        );

        $this->addNotification('user.edit.success');

        return $this->redirect->back();
    }
}
