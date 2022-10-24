<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Helpers\Authenticator;
use Psr\Log\LoggerInterface;

class SettingsController extends BaseController
{
    use HasUserNotifications;

    /** @var Authenticator */
    protected $auth;

    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $log;

    /** @var Redirector */
    protected $redirect;

    /** @var Response */
    protected $response;

    /** @var string[] */
    protected $permissions = [
        'user_settings',
    ];

    /**
     * @param Config   $config
     * @param Response $response
     */
    public function __construct(
        Authenticator $auth,
        Config $config,
        LoggerInterface $log,
        Redirector $redirector,
        Response $response
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->log = $log;
        $this->redirect = $redirector;
        $this->response = $response;
    }

    public function profile(): Response
    {
        $user = $this->auth->user();

        return $this->response->withView(
            'pages/settings/profile',
            [
                'settings_menu' => $this->settingsMenu(),
                'user' => $user,
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function saveProfile(Request $request): Response
    {
        $user = $this->auth->user();
        $data = $this->validate($request, [
            'pronoun'                => 'optional|max:15',
            'first_name'             => 'optional|max:64',
            'last_name'              => 'optional|max:64',
            'planned_arrival_date'   => 'required|date:Y-m-d',
            'planned_departure_date' => 'optional|date:Y-m-d',
            'dect'                   => 'optional|length:0:40', // dect/mobile can be purely numbers. "max" would have
            'mobile'                 => 'optional|length:0:40', // checked their values, not their character length.
            'email'                  => 'required|email|max:254',
            'email_shiftinfo'        => 'optional|checked',
            'email_news'             => 'optional|checked',
            'email_human'            => 'optional|checked',
            'email_goody'            => 'optional|checked',
            'shirt_size'             => 'required',
        ]);

        if (config('enable_pronoun')) {
            $user->personalData->pronoun = $data['pronoun'];
        }

        if (config('enable_user_name')) {
            $user->personalData->first_name = $data['first_name'];
            $user->personalData->last_name = $data['last_name'];
        }

        if (config('enable_planned_arrival')) {
            $user->personalData->planned_arrival_date = $data['planned_arrival_date'];
            $user->personalData->planned_departure_date = $data['planned_departure_date'];
        }

        if (config('enable_dect')) {
            $user->contact->dect = $data['dect'];
        }

        $user->contact->mobile = $data['mobile'];
        $user->email = $data['email'];
        $user->settings->email_shiftinfo = $data['email_shiftinfo'];
        $user->settings->email_news = $data['email_news'];
        $user->settings->email_human = $data['email_human'];

        if (config('enable_goody')) {
            $user->settings->email_goody = $data['email_goody'];
        }

        $user->personalData->shirt_size = $data['shirt_size'];

        $user->personalData->save();
        $user->contact->save();
        $user->settings->save();
        $user->save();

        $this->addNotification('settings.profile.success');

        return $this->redirect->to('/settings/profile');
    }

    /**
     * @return Response
     */
    public function password(): Response
    {
        return $this->response->withView(
            'pages/settings/password',
            [
                'settings_menu' => $this->settingsMenu(),
                'min_length'    => config('min_password_length')
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
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
            $this->addNotification('auth.password.error', 'errors');
        } elseif ($data['new_password'] != $data['new_password2']) {
            $this->addNotification('validation.password.confirmed', 'errors');
        } else {
            $this->auth->setPassword($user, $data['new_password']);

            $this->addNotification('settings.password.success');
            $this->log->info('User set new password.');
        }

        return $this->redirect->to('/settings/password');
    }

    /**
     * @return Response
     */
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
                'current_theme' => $currentTheme
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
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

    /**
     * @return Response
     */
    public function language(): Response
    {
        $languages = config('locales');

        $currentLanguage = $this->auth->user()->settings->language;

        return $this->response->withView(
            'pages/settings/language',
            [
                'settings_menu'    => $this->settingsMenu(),
                'languages'        => $languages,
                'current_language' => $currentLanguage
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
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

    /**
     * @return Response
     */
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
            ] + $this->getNotifications(),
        );
    }

    /**
     * @return array
     */
    public function settingsMenu(): array
    {
        $menu = [
            url('/user-settings')     => 'settings.profile',
            url('/settings/password') => 'settings.password',
            url('/settings/language') => 'settings.language',
            url('/settings/theme')    => 'settings.theme'
        ];

        if (!empty(config('oauth'))) {
            $menu[url('/settings/oauth')] = ['title' => 'settings.oauth', 'hidden' => $this->checkOauthHidden()];
        }

        return $menu;
    }

    /**
     * @return bool
     */
    protected function checkOauthHidden(): bool
    {
        foreach (config('oauth') as $config) {
            if (empty($config['hidden']) || !$config['hidden']) {
                return false;
            }
        }

        return true;
    }
}
