<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class SettingsController extends BaseController
{
    use HasUserNotifications;
    use ChecksArrivalsAndDepartures;

    /** @var string[] */
    protected array $permissions = [
        'user_settings',
        'api' => 'api||shifts_json_export||ical||atom',
        'apiKeyReset' => 'api||shifts_json_export||ical||atom',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Config $config,
        protected LoggerInterface $log,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function profile(): Response
    {
        $user = $this->auth->user();
        $requiredFields = $this->config->get('required_user_fields');

        return $this->response->withView(
            'pages/settings/profile',
            [
                'settings_menu' => $this->settingsMenu(),
                'userdata' => $user,
                'goodie_enabled' => $this->config->get('goodie_type') !== GoodieType::None->value
                    && config('enable_email_goodie'),
                'goodie_tshirt' => $this->config->get('goodie_type') === GoodieType::Tshirt->value,
                'voucherEnabled' =>  $this->config->get('enable_voucher'),
                'forceFoodEnabled' => $this->config->get('enable_force_food'),
                'tShirtLink' => $this->config->get('tshirt_link'),
                'isPronounRequired' => $requiredFields['pronoun'],
                'isFirstnameRequired' => $requiredFields['firstname'],
                'isLastnameRequired' => $requiredFields['lastname'],
                'isTShirtSizeRequired' => $requiredFields['tshirt_size'],
                'isMobileRequired' => $requiredFields['mobile'],
                'isDectRequired' => $requiredFields['dect'],
            ]
        );
    }

    public function saveProfile(Request $request): Response
    {
        $user = $this->auth->user();
        $data = $this->validate($request, $this->getSaveProfileRules($user));
        $goodie = GoodieType::from(config('goodie_type'));
        $goodie_enabled = $goodie !== GoodieType::None;
        $goodie_tshirt = $goodie === GoodieType::Tshirt;

        if (config('enable_pronoun')) {
            $user->personalData->pronoun = $data['pronoun'];
        }

        if (config('enable_full_name')) {
            $user->personalData->first_name = $data['first_name'];
            $user->personalData->last_name = $data['last_name'];
        }

        if (config('enable_planned_arrival')) {
            if (!$this->isArrivalDateValid($data['planned_arrival_date'], $data['planned_departure_date'])) {
                $this->addNotification('settings.profile.planned_arrival_date.invalid', NotificationType::ERROR);
                return $this->redirect->to('/settings/profile');
            } elseif (!$this->isDepartureDateValid($data['planned_arrival_date'], $data['planned_departure_date'])) {
                $this->addNotification('settings.profile.planned_departure_date.invalid', NotificationType::ERROR);
                return $this->redirect->to('/settings/profile');
            } else {
                $user->personalData->planned_arrival_date = $data['planned_arrival_date'];
                $user->personalData->planned_departure_date = $data['planned_departure_date'] ?: null;
            }
        }

        if (config('enable_dect')) {
            $user->contact->dect = $data['dect'];
        }

        $user->contact->mobile = $data['mobile'];

        if (config('enable_mobile_show')) {
            $user->settings->mobile_show = $data['mobile_show'] ?: false;
        }

        $user->email = $data['email'];
        $user->settings->email_shiftinfo = $data['email_shiftinfo'] ?: false;
        $user->settings->email_news = $data['email_news'] ?: false;
        $user->settings->email_human = $data['email_human'] ?: false;
        $user->settings->email_messages = $data['email_messages'] ?: false;

        if (config('enable_voucher') && config('enable_force_food')) {
            $user->settings->email_food = $data['email_food'] ?: false;
        }

        if ($goodie_enabled && config('enable_email_goodie')) {
            $user->settings->email_goodie = $data['email_goodie'] ?: false;
        }

        if (
            $goodie_tshirt
            && !$user->state->got_goodie
            && (isset(config('tshirt_sizes')[$data['shirt_size'] ?? '']) || is_null($data['shirt_size']))
        ) {
            $user->personalData->shirt_size = $data['shirt_size'];
        }

        $user->personalData->save();
        $user->contact->save();
        $user->settings->save();
        $user->save();

        $this->addNotification('settings.success');

        return $this->redirect->to('/settings/profile');
    }

    public function password(): Response
    {
        return $this->response->withView(
            'pages/settings/password',
            [
                'settings_menu' => $this->settingsMenu(),
                'min_length'    => config('password_min_length'),
            ]
        );
    }

    public function savePassword(Request $request): Response
    {
        $user = $this->auth->user();

        $minLength = config('password_min_length');
        $data = $this->validate($request, [
            'password'      => empty($user->password) ? 'optional' : 'required',
            'new_password'  => 'required|length:' . $minLength,
            'new_password2' => 'required',
        ]);

        if (!empty($user->password) && !$this->auth->verifyPassword($user, $data['password'])) {
            $this->addNotification('auth.password.error', NotificationType::ERROR);
        } elseif ($data['new_password'] != $data['new_password2']) {
            $this->addNotification('validation.password.confirmed', NotificationType::ERROR);
        } else {
            $this->auth->setPassword($user, $data['new_password']);

            $this->addNotification('settings.password.success');
            $this->log->info('User set new password.');

            $user->sessions()
                ->getQuery()
                ->where('id', '!=', session()->getId())
                ->delete();
        }

        return $this->redirect->to('/settings/password');
    }

    public function theme(): Response
    {
        $themes = array_map(function ($theme) {
            return $theme['name'];
        }, config('themes'));

        $currentTheme = $this->auth->user()->settings->theme;

        return $this->response->withView(
            'pages/settings/theme',
            [
                'settings_menu' => $this->settingsMenu(),
                'themes'        => $themes,
                'current_theme' => $currentTheme,
            ]
        );
    }

    public function saveTheme(Request $request): Response
    {
        $user = $this->auth->user();
        $data = $this->validate($request, ['select_theme' => 'int']);
        $selectTheme = $data['select_theme'];

        if (!isset(config('themes')[$selectTheme])) {
            throw new HttpNotFound('Theme with id ' . $selectTheme . ' does not exist.');
        }

        $user->settings->theme = $selectTheme;
        $user->settings->save();

        $this->addNotification('settings.theme.success');

        return $this->redirect->to('/settings/theme');
    }

    public function language(): Response
    {
        $languages = config('locales');

        $currentLanguage = $this->auth->user()->settings->language;

        return $this->response->withView(
            'pages/settings/language',
            [
                'settings_menu'    => $this->settingsMenu(),
                'languages'        => $languages,
                'current_language' => $currentLanguage,
            ]
        );
    }

    public function saveLanguage(Request $request): Response
    {
        $user = $this->auth->user();
        $data = $this->validate($request, ['select_language' => 'required']);
        $selectLanguage = $data['select_language'];

        if (!isset(config('locales')[$selectLanguage])) {
            throw new HttpNotFound('Language ' . $selectLanguage . ' does not exist.');
        }

        $user->settings->language = $selectLanguage;
        $user->settings->save();

        session()->set('locale', $selectLanguage);

        $this->addNotification('settings.language.success');

        return $this->redirect->to('/settings/language');
    }

    public function certificate(): Response
    {
        if (
            !(config('ifsg_enabled') && $this->checkIfsgCertificate())
            && !(config('driving_license_enabled') && $this->checkDrivingLicense())
        ) {
            throw new HttpNotFound();
        }

        $user = $this->auth->user();
        return $this->response->withView(
            'pages/settings/certificates',
            [
                'settings_menu' => $this->settingsMenu(),
                'driving_license' => $this->checkDrivingLicense(),
                'ifsg' => $this->checkIfsgCertificate(),
                'certificates' => $user->license,
            ]
        );
    }

    public function saveIfsgCertificate(Request $request): Response
    {
        $user = $this->auth->user();
        if (!config('ifsg_enabled') || $user->license->ifsg_confirmed || !$this->checkIfsgCertificate()) {
            throw new HttpNotFound();
        }

        $data = $this->validate($request, [
            'ifsg_certificate_light' => 'optional|checked',
            'ifsg_certificate' => 'optional|checked',
        ]);

        if (config('ifsg_light_enabled')) {
            $user->license->ifsg_certificate_light = !$data['ifsg_certificate'] && $data['ifsg_certificate_light'];
        }
        $user->license->ifsg_certificate = (bool) $data['ifsg_certificate'];

        $user->license->save();
        $this->addNotification('settings.certificates.success');

        return $this->redirect->to('/settings/certificates');
    }

    public function saveDrivingLicense(Request $request): Response
    {
        if (!config('driving_license_enabled') || !$this->checkDrivingLicense()) {
            throw new HttpNotFound();
        }

        $user = $this->auth->user();
        $data = $this->validate($request, [
            'has_car' => 'optional|checked',
            'drive_car' => 'optional|checked',
            'drive_3_5t' => 'optional|checked',
            'drive_7_5t' => 'optional|checked',
            'drive_12t' => 'optional|checked',
            'drive_forklift' => 'optional|checked',
        ]);

        $user->license->has_car = (bool) $data['has_car'];
        if (!$user->license->drive_confirmed) {
            $user->license->drive_car = (bool) $data['drive_car'];
            $user->license->drive_3_5t = (bool) $data['drive_3_5t'];
            $user->license->drive_7_5t = (bool) $data['drive_7_5t'];
            $user->license->drive_12t = (bool) $data['drive_12t'];
            $user->license->drive_forklift = (bool) $data['drive_forklift'];
        }
        $user->license->save();

        $this->addNotification('settings.certificates.success');

        return $this->redirect->to('/settings/certificates');
    }

    public function api(): Response
    {
        return $this->response->withView(
            'pages/settings/api',
            [
                'settings_menu' => $this->settingsMenu(),
            ],
        );
    }

    public function apiKeyReset(): Response
    {
        $this->auth->resetApiKey($this->auth->user());

        $this->addNotification('settings.api.key_reset_success');
        return $this->redirect->back();
    }

    public function oauth(): Response
    {
        $providers = $this->config->get('oauth');
        if (empty($providers)) {
            throw new HttpNotFound();
        }

        return $this->response->withView(
            'pages/settings/oauth',
            [
                'settings_menu' => $this->settingsMenu(),
                'providers'     => $providers,
            ],
        );
    }

    public function sessions(): Response
    {
        $sessions = $this->auth->user()->sessions->sortByDesc('last_activity');

        return $this->response->withView(
            'pages/settings/sessions',
            [
                'settings_menu' => $this->settingsMenu(),
                'sessions' => $sessions,
                'current_session' => session()->getId(),
            ],
        );
    }

    public function sessionsDelete(Request $request): Response
    {
        $id = $request->postData('id');
        $query = $this->auth->user()
            ->sessions()
            ->getQuery()
            ->where('id', '!=', session()->getId());

        if ($id != 'all') {
            $this->validate($request, [
                'id' => 'required|alnum|length:15:15',
            ]);
            $query = $query->where('id', 'LIKE', $id . '%');
        }

        $query->delete();
        $this->addNotification('settings.sessions.delete_success');

        return $this->redirect->to('/settings/sessions');
    }

    public function settingsMenu(): array
    {
        $menu = [
            url('/users', ['action' => 'view']) => ['title' => 'profile.my_shifts', 'icon' => 'chevron-left'],
            url('/settings/profile')  => 'settings.profile',
            url('/settings/password') => ['title' => 'settings.password', 'icon' => 'key-fill'],
        ];

        if (count(config('locales')) > 1) {
            $menu[url('/settings/language')] = ['title' => 'settings.language', 'icon' => 'translate'];
        }

        if (count(config('themes')) > 1) {
            $menu[url('/settings/theme')] = 'settings.theme';
        }

        if (
            (config('ifsg_enabled') && $this->checkIfsgCertificate())
            || (config('driving_license_enabled') && $this->checkDrivingLicense())
        ) {
            $menu[url('/settings/certificates')] = ['title' => 'settings.certificates', 'icon' => 'card-checklist'];
        }

        $menu[url('/settings/sessions')] = 'settings.sessions';

        if (!empty(config('oauth'))) {
            $menu[url('/settings/oauth')] = ['title' => 'settings.oauth', 'hidden' => $this->checkOauthHidden()];
        }

        if ($this->auth->canAny(['api', 'shifts_json_export', 'ical', 'atom'])) {
            $menu[url('/settings/api')] = ['title' => 'settings.api', 'icon' => 'braces'];
        }

        return $menu;
    }

    protected function checkOauthHidden(): bool
    {
        $userServices = $this->auth->user()->oauth;
        foreach (config('oauth') as $name => $config) {
            if (empty($config['hidden']) || $userServices->contains('provider', $name)) {
                return false;
            }
        }

        return true;
    }

    protected function checkDrivingLicense(): bool
    {
        return $this->auth->user()->userAngelTypes->filter(function (AngelType $angelType) {
            return $angelType->requires_driver_license;
        })->isNotEmpty();
    }

    protected function checkIfsgCertificate(): bool
    {
        return $this->auth->user()->userAngelTypes->filter(function (AngelType $angelType) {
            return $angelType->requires_ifsg_certificate;
        })->isNotEmpty();
    }

    private function isRequired(string $key): string
    {
        $requiredFields = $this->config->get('required_user_fields');
        return $requiredFields[$key] ? 'required' : 'optional';
    }

    /**
     * @return string[]
     */
    private function getSaveProfileRules(User $user): array
    {
        $goodie_tshirt = $this->config->get('goodie_type') === GoodieType::Tshirt->value;
        $rules = [
            'pronoun' => $this->isRequired('pronoun') . '|max:15',
            'first_name' => $this->isRequired('firstname') . '|max:64',
            'last_name' => $this->isRequired('lastname') . '|max:64',
            'dect' => $this->isRequired('dect') . '|length:0:40',
            // dect/mobile can be purely numbers. "max" would have checked their values, not their character length.
            'mobile' => $this->isRequired('mobile') . '|length:0:40',
            'mobile_show' => 'optional|checked',
            'email' => 'required|email|max:254',
            'email_shiftinfo' => 'optional|checked',
            'email_news' => 'optional|checked',
            'email_human' => 'optional|checked',
            'email_messages' => 'optional|checked',
            'email_goodie' => 'optional|checked',
        ];
        if (config('enable_planned_arrival')) {
            $rules['planned_arrival_date'] = 'required|date:Y-m-d';
            $rules['planned_departure_date'] = 'optional|date:Y-m-d';
        }
        if ($goodie_tshirt && !$user->state->got_goodie) {
            $rules['shirt_size'] = $this->isRequired('tshirt_size') . '|shirt_size';
        }
        if (config('enable_voucher') && config('enable_force_food')) {
            $rules['email_food'] = 'optional|checked';
        }
        return $rules;
    }
}
