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

    /**
     * @return Response
     */
    public function password(): Response
    {
        return $this->response->withView(
            'pages/settings/password.twig',
            $this->getNotifications()
        );
    }

    /**
     * @return Response
     */
    public function savePassword(Request $request): Response
    {
        $user = $this->auth->user();

        if (
            !$request->has('password')
            || !$this->auth->verifyPassword($user, $request->postData('password'))
        ) {
            $this->addNotification('-> not OK. Please try again.', 'errors');
        } elseif (strlen($request->postData('new_password')) < config('min_password_length')) {
            $this->addNotification('Your password is to short (please use at least 6 characters).', 'errors');
        } elseif ($request->postData('new_password') != $request->postData('new_password2')) {
            $this->addNotification('Your passwords don\'t match.', 'errors');
        } else {
            $this->auth->setPassword($user, $request->postData('new_password'));

            $this->addNotification('Password saved.');
            $this->log->info('User set new password.');
        }

        return $this->redirect->to('/settings/password');
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
            'pages/settings/oauth.twig',
            [
                'providers' => $providers,
            ] + $this->getNotifications(),
        );
    }
}
