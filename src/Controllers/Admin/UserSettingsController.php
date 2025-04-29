<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class UserSettingsController extends BaseController
{
    use HasUserNotifications;

    public function __construct(
        protected Authenticator $auth,
        protected Config $config,
        protected LoggerInterface $log,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function certificate(Request $request): Response
    {
        if (!config('ifsg_enabled') && !config('driving_license_enabled')) {
            throw new HttpNotFound();
        }

        if (
            !(
                $this->auth->canAny(['user.ifsg.edit', 'user.drive.edit'])
                || $this->isDriverLicenseSupporter()
                || $this->isIfsgSupporter()
            )
        ) {
            throw new HttpForbidden();
        }

        $user = $this->getUser($request);

        return $this->view(
            $user,
            'pages/settings/certificates-admin',
            [
                'certificates' => $user->license,
            ]
        );
    }

    public function saveIfsgCertificate(Request $request): Response
    {
        if (!config('ifsg_enabled')) {
            throw new HttpNotFound();
        }

        $this->checkPermission('user.ifsg.edit', $this->isIfsgSupporter());
        $user = $this->getUser($request);

        $data = $this->validate($request, [
            'ifsg_certificate_light' => 'optional|checked',
            'ifsg_certificate' => 'optional|checked',
            'ifsg_confirmed' => 'optional|checked',
        ]);

        if (config('ifsg_light_enabled')) {
            $user->license->ifsg_certificate_light = !$data['ifsg_certificate'] && $data['ifsg_certificate_light'];
        }
        $user->license->ifsg_certificate = (bool) $data['ifsg_certificate'];
        $user->license->ifsg_confirmed = $data['ifsg_confirmed']
            && ($user->license->ifsg_certificate || $user->license->ifsg_certificate_light);

        $user->license->save();
        $this->addNotification('settings.certificates.success');
        $this->log->info('Certificate "{certificate}" of user {user} ({id}) is {confirmation}.', [
            'certificate' => $user->license->ifsg_certificate_light
                ? 'IfSG light'
                : ($user->license->ifsg_certificate
                    ? 'IfSG'
                    : 'no IfSG'
                ),
            'user' => $user->name,
            'id' => $user->id,
            'confirmation' => $user->license->ifsg_confirmed ? 'confirmed' : 'unconfirmed',
        ]);

        return $this->redirect->to('/users/' . $user->id . '/certificates');
    }

    public function saveDrivingLicense(Request $request): Response
    {
        if (!config('driving_license_enabled')) {
            throw new HttpNotFound();
        }

        $this->checkPermission('user.drive.edit', $this->isDriverLicenseSupporter());
        $user = $this->getUser($request);

        $data = $this->validate($request, [
            'drive_car' => 'optional|checked',
            'drive_3_5t' => 'optional|checked',
            'drive_7_5t' => 'optional|checked',
            'drive_12t' => 'optional|checked',
            'drive_forklift' => 'optional|checked',
            'drive_confirmed' => 'optional|checked',
        ]);

        $user->license->drive_car = (bool) $data['drive_car'];
        $user->license->drive_3_5t = (bool) $data['drive_3_5t'];
        $user->license->drive_7_5t = (bool) $data['drive_7_5t'];
        $user->license->drive_12t = (bool) $data['drive_12t'];
        $user->license->drive_forklift = (bool) $data['drive_forklift'];
        $user->license->drive_confirmed = $data['drive_confirmed'] && (
            $user->license->drive_car
            || $user->license->drive_3_5t
            || $user->license->drive_7_5t
            || $user->license->drive_12t
            || $user->license->drive_forklift
        );

        $user->license->save();
        $this->addNotification('settings.certificates.success');

        $this->log->info('Certificate "{certificate}" of user {user} ({id}) is {confirmation}.', [
            'certificate' => ($user->license->drive_car ? 'car' : '')
                . ($user->license->drive_3_5t ? ', 3.5t' : '')
                . ($user->license->drive_7_5t ? ', 7.5t' : '')
                . ($user->license->drive_12t ? ', 12t' : '')
                . ($user->license->drive_forklift ? ', forklift' : ''),
            'user' => $user->name,
            'id' => $user->id,
            'confirmation' => $user->license->drive_confirmed ? 'confirmed' : 'unconfirmed',
        ]);

        return $this->redirect->to('/users/' . $user->id . '/certificates');
    }

    public function settingsMenu(User $user): array
    {
        $menu = [
            url('/users', ['action' => 'view', 'user_id' => $user->id]) => [
                'title' => 'general.back', 'icon' => 'chevron-left',
            ],
        ];

        if (config('ifsg_enabled') || config('driving_license_enabled')) {
            $menu[url('/users/' . $user->id . '/certificates')] = [
                'title' => 'settings.certificates',
                'icon' => 'card-checklist',
                'permission' => (
                    $this->auth->canAny(['user.ifsg.edit', 'user.drive.edit'])
                    || $this->isIfsgSupporter()
                    || $this->isDriverLicenseSupporter()
                ) ? null : '_',
            ];
        }

        return $menu;
    }

    protected function checkPermission(string | array $abilities, bool $overwrite = false): void
    {
        if (!$overwrite && !$this->auth->can($abilities)) {
            throw new HttpForbidden();
        }
    }

    protected function view(User $user, string $view, array $data = []): Response
    {
        return $this->response->withView(
            $view,
            array_merge([
                'settings_menu' => $this->settingsMenu($user),
                'is_admin' => true,
                'ifsg' => $this->isIfsgSupporter() || $this->auth->can('user.ifsg.edit'),
                'driver_license' => $this->isDriverLicenseSupporter() || $this->auth->can('user.drive.edit'),
                'admin_user' => $user,
            ], $data)
        );
    }

    protected function getUser(Request $request): User
    {
        $userId = $request->getAttribute('user_id');
        return User::findOrFail($userId);
    }

    public function isIfsgSupporter(): bool
    {
        return (bool) AngelType::whereRequiresIfsgCertificate(true)
            ->leftJoin('user_angel_type', 'user_angel_type.angel_type_id', 'angel_types.id')
            ->where('user_angel_type.user_id', $this->auth->user()?->id)
            ->where('user_angel_type.supporter', true)
            ->count();
    }

    public function isDriverLicenseSupporter(): bool
    {
        return (bool) AngelType::whereRequiresDriverLicense(true)
            ->leftJoin('user_angel_type', 'user_angel_type.angel_type_id', 'angel_types.id')
            ->where('user_angel_type.user_id', $this->auth->user()?->id)
            ->where('user_angel_type.supporter', true)
            ->count();
    }
}
