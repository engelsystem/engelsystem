<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Psr\Log\LoggerInterface;

class UserVoucherController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'voucher.edit',
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

    private function checkActive(): void
    {
        if (!config('enable_voucher')) {
            throw new HttpNotFound();
        }
    }

    public function editVoucher(Request $request): Response
    {
        $this->checkActive();
        $userId = (int) $request->getAttribute('user_id');

        /** @var User $user */
        $user = $this->user->findOrFail($userId);

        return $this->response->withView(
            'admin/user/edit-voucher.twig',
            [
                'userdata' => $user,
                'gotVoucher' => $user->state->got_voucher ?? 0,
                'forceActive' => $user->state->force_active && config('enable_force_active'),
                'forceFood' => $user->state->force_food && config('enable_force_food'),
                'eligibleVoucherCount' => UserVouchers::eligibleVoucherCount($user),
            ]
        );
    }

    public function saveVoucher(Request $request): Response
    {
        $this->checkActive();
        $userId = (int) $request->getAttribute('user_id');
        /** @var User $user */
        $user = $this->user->findOrFail($userId);

        $data = $this->validate($request, [
            'got_voucher' => 'int|min:0',
        ]);

        $user->state->got_voucher = (int) $data['got_voucher'];
        $user->state->save();

        $this->log->info(
            '{name} ({id}) got {got_voucher} vouchers.',
            [
                'name' => $user->name,
                'id' => $user->id,
                'got_voucher' => $user->state->got_voucher,
            ]
        );

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            // This was an async request, send a JSON response.
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withContent(json_encode([
                'issued' => $user->state->got_voucher,
                'eligible' => $user->state->got_voucher + UserVouchers::eligibleVoucherCount($user),
                'total' => (int) State::query()->sum('got_voucher'),
            ]));
        }

        $this->addNotification('voucher.save.success');

        return $this->redirect->to('/users?action=view&user_id=' . $user->id);
        // TODO Once User_view.php gets removed, change this to withView + getNotifications
    }
}
