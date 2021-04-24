<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;

class FaqController extends BaseController
{
    use HasUserNotifications;

    /** @var Config */
    protected $config;

    /** @var Faq */
    protected $faq;

    /** @var Response */
    protected $response;

    /** @var string[] */
    protected $permissions = [
        'faq.view',
    ];

    /**
     * @param Config          $config
     * @param Faq             $faq
     * @param Response        $response
     */
    public function __construct(
        Config $config,
        Faq $faq,
        Response $response
    ) {
        $this->config = $config;
        $this->faq = $faq;
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        $text = $this->config->get('faq_text');

        $faq = $this->faq->orderBy('question')->get();

        return $this->response->withView(
            'pages/faq/overview.twig',
            ['text' => $text, 'items' => $faq] + $this->getNotifications()
        );
    }
}
