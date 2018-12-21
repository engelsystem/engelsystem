<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Response;

class CreditsController extends BaseController
{
    /** @var Config */
    protected $config;

    /** @var Response */
    protected $response;

    /**
     * @param Response $response
     * @param Config   $config
     */
    public function __construct(Response $response, Config $config)
    {
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->response->withView(
            'pages/credits.twig',
            ['credits' => $this->config->get('credits')]
        );
    }
}
