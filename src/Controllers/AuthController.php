<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Confirmation;
use Engelsystem\Http\Exceptions\HttpNotFound;
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
        protected Authenticator $auth,
        protected Confirmation $confirm,
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

        if ($this->confirm->confirmationAvailable() && !$user->state->email_confirmed) {
            if ($this->confirm->sendEmailConfirmationLink($user)) {
                $this->addNotification('auth.email-confirmation-request', NotificationType::WARNING);
            } else {
                $this->addNotification('auth.unable-to-confirm', NotificationType::ERROR);
            }

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
        $this->session->migrate(true, (int)($this->config->get('session')['lifetime']));

        $user->last_login_at = new Carbon();
        $user->save(['touch' => false]);

        return $this->redirect->to($previousPage ?: $this->config->get('home_site'));
    }

    public function logout(): Response
    {
        $this->session->invalidate();

        return $this->redirect->to('/');
    }

    public function confirmEmail(Request $request): Response
    {
        if (!$this->confirm->confirmationAvailable()) {
            throw new HttpNotFound();
        }

        if ($this->confirm->confirmEmailAddress($request->getAttribute('token'))) {
            $this->addNotification('auth.confirmed', NotificationType::INFORMATION);
        } else {
            $this->addNotification('auth.unable-to-confirm', NotificationType::ERROR);
        }

        return $this->redirect->to('/');
    }
}
