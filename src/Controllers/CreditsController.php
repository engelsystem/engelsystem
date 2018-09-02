<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class CreditsController extends BaseController
{
    /** @var Response */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->response->withView('pages/credits.twig');
    }
}
