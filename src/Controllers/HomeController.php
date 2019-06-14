<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;

class HomeController extends BaseController
{
    /**
     * @var Authenticator
     */
    protected $auth;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Authenticator $auth
     * @param Config        $config
     */
    public function __construct(Authenticator $auth, Config $config)
    {
        $this->auth = $auth;
        $this->config = $config;
    }

    /**
     * @throws HttpTemporaryRedirect
     */
    public function index()
    {
        throw new HttpTemporaryRedirect($this->auth->user() ? $this->config->get('home_site') : 'login');
    }
}
