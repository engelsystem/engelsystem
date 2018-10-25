<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends BaseController
{
    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var UrlGeneratorInterface */
    protected $url;

    public function __construct(Response $response, SessionInterface $session, UrlGeneratorInterface $url)
    {
        $this->response = $response;
        $this->session = $session;
        $this->url = $url;
    }

    /**
     * @return Response
     */
    public function logout()
    {
        $this->session->invalidate();

        return $this->response->redirectTo($this->url->to('/'));
    }
}
