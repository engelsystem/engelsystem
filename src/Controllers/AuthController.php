<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
    public function login(): Response
    {
        return $this->showLogin();
    }

    /**
     * @return Response
     */
    protected function showLogin(): Response
    {
        $errors = Collection::make(Arr::flatten($this->session->get('errors', [])));
        $this->session->remove('errors');

        return $this->response->withView(
            'pages/login',
            ['errors' => $errors]
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
            $this->session->set('errors', $this->session->get('errors', []) + ['auth.not-found']);

            return $this->showLogin();
        }

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
}
