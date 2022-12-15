<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;

class FaqController extends BaseController
{
    use HasUserNotifications;

    protected Config $config;

    protected Faq $faq;

    protected Response $response;

    /** @var string[] */
    protected array $permissions = [
        'faq.view',
    ];

    public function __construct(
        Config $config,
        Faq $faq,
        Response $response
    ) {
        $this->config = $config;
        $this->faq = $faq;
        $this->response = $response;
    }

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
