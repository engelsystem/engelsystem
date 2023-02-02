<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;

class FaqController extends BaseController
{
    use HasUserNotifications;

    /** @var string[] */
    protected array $permissions = [
        'faq.view',
    ];

    public function __construct(
        protected Config $config,
        protected Faq $faq,
        protected Response $response
    ) {
    }

    public function index(): Response
    {
        $text = $this->config->get('faq_text');

        $faq = $this->faq->orderBy('question')->get();

        return $this->response->withView(
            'pages/faq/overview.twig',
            ['text' => $text, 'items' => $faq]
        );
    }
}
