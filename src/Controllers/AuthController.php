<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\User\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends BaseController
{
    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var UrlGeneratorInterface */
    protected $url;

    /** @var Authenticator */
    protected $auth;

    /** @var array */
    protected $permissions = [
        'login'     => 'login',
        'postLogin' => 'login',
    ];

    /**
     * @param Response              $response
     * @param SessionInterface      $session
     * @param UrlGeneratorInterface $url
     * @param Authenticator         $auth
     */
    public function __construct(
        Response $response,
        SessionInterface $session,
        UrlGeneratorInterface $url,
        Authenticator $auth
    ) {
        $this->response = $response;
        $this->session = $session;
        $this->url = $url;
        $this->auth = $auth;
    }

    /**
     * @return Response
     */
    public function login()
    {
        return $this->response->withView('pages/login');
    }

    /**
     * Posted login form
     *
     * @param Request $request
     * @return Response
     */
    public function postLogin(Request $request): Response
    {
        $return = $this->authenticateUser($request->get('login', ''), $request->get('password', ''));
        if (!$return instanceof User) {
            return $this->response->withView(
                'pages/login',
                ['errors' => [$return], 'show_password_recovery' => true]
            );
        }

        $user = $return;

        $this->session->invalidate();
        $this->session->set('user_id', $user->id);
        $this->session->set('locale', $user->settings->language);

        $user->last_login_at = new Carbon();
        $user->save(['touch' => false]);

        return $this->response->redirectTo('news');
    }

    /**
     * @return Response
     */
    public function logout(): Response
    {
        $this->session->invalidate();

        return $this->response->redirectTo($this->url->to('/'));
    }

    /**
     * Verify the user and password
     *
     * @param $login
     * @param $password
     * @return User|string
     */
    protected function authenticateUser(string $login, string $password)
    {
        if (!$login) {
            return 'auth.no-nickname';
        }

        if (!$password) {
            return 'auth.no-password';
        }

        if (!$user = $this->auth->authenticate($login, $password)) {
            return 'auth.not-found';
        }

        return $user;
    }
}
