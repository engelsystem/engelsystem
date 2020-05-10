<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;

class HomeController extends BaseController
{
    /** @var Authenticator */
    protected $auth;

    /** @var Config */
    protected $config;

    /** @var Redirector */
    protected $redirect;

    /**
     * @param Authenticator $auth
     * @param Config        $config
     * @param Redirector    $redirect
     */
    public function __construct(Authenticator $auth, Config $config, Redirector $redirect)
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->redirect = $redirect;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->redirect->to($this->auth->user() ? $this->config->get('home_site') : 'login');
    }
}
