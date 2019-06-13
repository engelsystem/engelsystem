<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;

class HomeController extends BaseController
{
    /**
     * @throws HttpTemporaryRedirect
     */
    public function index()
    {
        throw new HttpTemporaryRedirect(auth()->user() ? config('home_site') : 'login');
    }
}
