<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class ApiController extends BaseController
{
    public function __construct(protected Response $response)
    {
    }

    public function index(): Response
    {
        return $this->response
            ->setStatusCode(501)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode(['error' => 'Not implemented']));
    }
}
