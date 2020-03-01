<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\User\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends BaseController
{
    use HasUserNotifications;

    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var UrlGeneratorInterface */
    protected $url;

    /** @var Config */
    protected $config;

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
     * @param Config                $config
     * @param Authenticator         $auth
     */
    public function __construct(
        Response $response,
        SessionInterface $session,
        UrlGeneratorInterface $url,
        Config $config,
        Authenticator $auth
    ) {
        $this->response = $response;
        $this->session = $session;
        $this->url = $url;
        $this->config = $config;
        $this->auth = $auth;
    }

    /**
     * @return Response
     */
    public function login(): Response
    {
        return $this->showLogin();
    }

    /**
     * @return Response
     */
    protected function showLogin(): Response
    {
        return $this->response->withView(
            'pages/login',
            $this->getNotifications()
        );
    }

    /**
     * Posted login form
     *
     * @param Request $request
     * @return Response
     */
    public function postLogin(Request $request): Response
    {
        $data = $this->validate($request, [
            'login'    => 'required',
            'password' => 'required',
        ]);

        $user = $this->auth->authenticate($data['login'], $data['password']);

        if (!$user instanceof User) {
            $this->addNotification('auth.not-found', 'errors');

            return $this->showLogin();
        }

        $this->session->invalidate();
        $this->session->set('user_id', $user->id);
        $this->session->set('locale', $user->settings->language);

        $user->last_login_at = new Carbon();
        $user->save(['touch' => false]);

        return $this->response->redirectTo($this->config->get('home_site'));
    }

    /**
     * @return Response
     */
    public function logout(): Response
    {
        $this->session->invalidate();

        return $this->response->redirectTo($this->url->to('/'));
    }
}
