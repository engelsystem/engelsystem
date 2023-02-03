<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;

class HealthController extends BaseController
{
    public function __construct(protected Response $response)
    {
    }

    public function index(): Response
    {
        return $this->response->withContent('Ok');
    }
}
