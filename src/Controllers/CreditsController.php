<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Version;
use Engelsystem\Http\Response;

class CreditsController extends BaseController
{
    public function __construct(protected Response $response, protected Config $config, protected Version $version)
    {
    }

    public function index(): Response
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
