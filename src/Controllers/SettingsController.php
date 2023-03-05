<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Helpers\Authenticator;
use Psr\Log\LoggerInterface;

class SettingsController extends BaseController
{
    use HasUserNotifications;
    use ChecksArrivalsAndDepartures;

    /** @var string[] */
    protected array $permissions = [
        'user_settings',
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

        return $this->response->withView(
            'pages/settings/profile',
            [
                'settings_menu' => $this->settingsMenu(),
                'user' => $user,
                'goodie_tshirt' => $this->config->get('goodie_type') === GoodieType::Tshirt->value,
                'goodie_enabled' => $this->config->get('goodie_type') !== GoodieType::None->value,
            ]
        );
    }

    public function saveProfile(Request $request): Response
    {
        $user = $this->auth->user();
        $data = $this->validate($request, $this->getSaveProfileRules());
        $goodie = GoodieType::from(config('goodie_type'));
        $goodie_enabled = $goodie !== GoodieType::None;
        $goodie_tshirt = $goodie === GoodieType::Tshirt;

        if (config('enable_pronoun')) {
            $user->personalData->pronoun = $data['pronoun'];
        }

        if (config('enable_user_name')) {
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

        if ($goodie_enabled) {
            $user->settings->email_goody = $data['email_goody'] ?: false;
        }

        if (
            $goodie_tshirt
            && isset(config('tshirt_sizes')[$data['shirt_size']])
        ) {
            $user->personalData->shirt_size = $data['shirt_size'];
        }

        $user->personalData->save();
        $user->contact->save();
        $user->settings->save();
        $user->save();

        $this->addNotification('settings.profile.success');

        return $this->redirect->to('/settings/profile');
    }

    public function password(): Response
    {
        return $this->response->withView(
            'pages/settings/password',
            [
                'settings_menu' => $this->settingsMenu(),
                'min_length'    => config('min_password_length'),
            ]
        );
    }

    public function savePassword(Request $request): Response
    {
        $user = $this->auth->user();

        $minLength = config('min_password_length');
        $data = $this->validate($request, [
            'password'      => 'required' . (empty($user->password) ? '|optional' : ''),
            'new_password'  => 'required|min:' . $minLength,
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

    public function settingsMenu(): array
    {
        $menu = [
            url('/settings/profile')  => 'settings.profile',
            url('/settings/password') => 'settings.password',
            url('/settings/language') => 'settings.language',
            url('/settings/theme')    => 'settings.theme',
        ];

        if (!empty(config('oauth'))) {
            $menu[url('/settings/oauth')] = ['title' => 'settings.oauth', 'hidden' => $this->checkOauthHidden()];
        }

        return $menu;
    }

    protected function checkOauthHidden(): bool
    {
        foreach (config('oauth') as $config) {
            if (empty($config['hidden']) || !$config['hidden']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getSaveProfileRules(): array
    {
        $goodie_tshirt = $this->config->get('goodie_type') === GoodieType::Tshirt->value;
        $rules = [
            'pronoun' => 'optional|max:15',
            'first_name' => 'optional|max:64',
            'last_name' => 'optional|max:64',
            'dect' => 'optional|length:0:40', // dect/mobile can be purely numbers. "max" would have
            'mobile' => 'optional|length:0:40', // checked their values, not their character length.
            'mobile_show' => 'optional|checked',
            'email' => 'required|email|max:254',
            'email_shiftinfo' => 'optional|checked',
            'email_news' => 'optional|checked',
            'email_human' => 'optional|checked',
            'email_messages' => 'optional|checked',
            'email_goody' => 'optional|checked',
        ];
        if (config('enable_planned_arrival')) {
            $rules['planned_arrival_date'] = 'required|date:Y-m-d';
            $rules['planned_departure_date'] = 'optional|date:Y-m-d';
        }
        if ($goodie_tshirt) {
            $rules['shirt_size'] = 'required';
        }
        return $rules;
    }
}
