<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;

class SettingsController extends BaseController
{
    use HasUserNotifications;

    /** @var Config */
    protected $config;

    /** @var Response */
    protected $response;

    /**
     * @param Config   $config
     * @param Response $response
     */
    public function __construct(
        Config $config,
        Response $response
    ) {
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function oauth(): Response
    {
        $providers = $this->config->get('oauth');
        if (empty($providers)) {
            throw new HttpNotFound();
        }

        return $this->response->withView(
            'pages/settings/oauth.twig',
            [
                'providers' => $providers,
            ] + $this->getNotifications(),
        );
    }
}
