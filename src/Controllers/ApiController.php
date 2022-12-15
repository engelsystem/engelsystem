<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class ApiController extends BaseController
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function index(): Response
    {
        return $this->response
            ->setStatusCode(501)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode(['error' => 'Not implemented']));
    }
}
