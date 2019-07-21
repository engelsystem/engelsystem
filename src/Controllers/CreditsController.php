<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Version;
use Engelsystem\Http\Response;

class CreditsController extends BaseController
{
    /** @var Config */
    protected $config;

    /** @var Response */
    protected $response;

    /** @var Version */
    protected $version;

    /**
     * @param Response $response
     * @param Config   $config
     * @param Version  $version
     */
    public function __construct(Response $response, Config $config, Version $version)
    {
        $this->config = $config;
        $this->response = $response;
        $this->version = $version;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->response->withView(
            'pages/credits.twig',
            [
                'credits' => $this->config->get('credits'),
                'version' => $this->version->getVersion(),
            ]
        );
    }
}
