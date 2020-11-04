<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class HealthController extends BaseController
{
    /** @var Response */
    protected $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->response->withContent('Ok');
    }
}
