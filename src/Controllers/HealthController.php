<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class HealthController extends BaseController
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function index(): Response
    {
        return $this->response->withContent('Ok');
    }
}
