<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string, string> */
    protected array $permissions = [
        'login'     => 'login',
        'postLogin' => 'login',
    ];

    public function __construct(
        protected Response $response,
        protected SessionInterface $session,
        protected Redirector $redirect,
        protected Config $config,
        protected Authenticator $auth
    ) {
    }

    public function login(): Response
    {
        return $this->showLogin();
    }

    protected function showLogin(): Response
    {
        return $this->response->withView('pages/login');
    }

    /**
     * Posted login form
     */
    public function postLogin(Request $request): Response
    {
        $data = $this->validate($request, [
            'login'    => 'required',
            'password' => 'required',
        ]);

        $user = $this->auth->authenticate($data['login'], $data['password']);

        if (!$user instanceof User) {
            $this->addNotification('auth.not-found', NotificationType::ERROR);

            return $this->showLogin();
        }

        return $this->loginUser($user);
    }

    public function loginUser(User $user): Response
    {
        $previousPage = $this->session->get('previous_page');

        $this->session->invalidate();
        $this->session->set('user_id', $user->id);
        $this->session->set('locale', $user->settings->language);

        $user->last_login_at = new Carbon();
        $user->save(['touch' => false]);

        return $this->redirect->to($previousPage ?: $this->config->get('home_site'));
    }

    public function logout(): Response
    {
        $this->session->invalidate();

        return $this->redirect->to('/');
    }
}
