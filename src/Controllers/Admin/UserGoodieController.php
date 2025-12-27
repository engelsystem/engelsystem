<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Carbon\CarbonInterval;
use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class UserGoodieController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string, string> */
    protected array $permissions = [
        'editGoodie' => 'user.goodie.edit',
        'saveGoodie' => 'user.goodie.edit',
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

    private function checkActive(): void
    {
        if (GoodieType::from(config('goodie_type')) == GoodieType::None) {
            throw new HttpNotFound();
        }
    }

    public function editGoodie(Request $request): Response
    {
        $this->checkActive();
        $userId = (int) $request->getAttribute('user_id');

        /** @var User $user */
        $user = $this->user->findOrFail($userId);
        $goodieScore = $user->state->force_active ? '~' :
            Carbon::formatDuration(
                CarbonInterval::minutes(round(Goodie::userScore($user) * 60)),
                __('general.duration')
            );

        return $this->response->withView(
            'admin/user/edit-goodie.twig',
            [
                'userdata' => $user,
                'is_tshirt' => $this->config->get('goodie_type') === GoodieType::Tshirt->value,
                'goodie_score' => $goodieScore,
            ]
        );
    }

    public function saveGoodie(Request $request): Response
    {
        $this->checkActive();
        $userId = (int) $request->getAttribute('user_id');
        $shirtEnabled = $this->config->get('goodie_type') === GoodieType::Tshirt->value;
        /** @var User $user */
        $user = $this->user->findOrFail($userId);

        $data = $this->validate($request, [
            'shirt_size' => ($shirtEnabled ? 'required' : 'optional') . '|shirt_size',
            'arrived'    => 'optional|checked',
            'active'     => 'optional|checked',
            'got_goodie' => 'optional|checked',
        ]);

        if ($shirtEnabled) {
            $user->personalData->shirt_size = $data['shirt_size'];
            $user->personalData->save();
        }

        if ($this->auth->can('admin_arrive')) {
            if ($user->state->arrived != (bool) $data['arrived']) {
                if ((bool) $data['arrived']) {
                    $user->state->arrival_date = new Carbon();
                } else {
                    $user->state->arrival_date = null;
                }
            }
        }

        $user->state->active = (bool) $data['active'];
        $user->state->got_goodie = (bool) $data['got_goodie'];
        $user->state->save();

        $this->log->info(
            'Updated user goodie state {user} ({id}): '
            . '{size}, arrived: {arrived}, active: {active}, got goodie: {got_goodie}',
            [
                'id'        => $user->id,
                'user'      => $user->name,
                'size'      => $user->personalData->shirt_size,
                'arrived'   => $user->state->arrived ? 'yes' : 'no',
                'active'    => $user->state->active ? 'yes' : 'no',
                'got_goodie' => $user->state->got_goodie ? 'yes' : 'no',
            ]
        );

        $this->addNotification('user.edit.success');

        return $this->redirect->back();
    }
}
