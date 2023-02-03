<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;

class HomeController extends BaseController
{
    public function __construct(protected Authenticator $auth, protected Config $config, protected Redirector $redirect)
    {
    }

    public function index(): Response
    {
        return $this->redirect->to($this->auth->user() ? $this->config->get('home_site') : 'login');
    }
}
