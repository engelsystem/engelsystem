<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

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
        $faq = $this->faq
            ->with('tags')
            ->orderBy('question')
            ->get();
        $tags = $faq->pluck('tags')
            ->flatten()
            ->unique('id')
            ->sortBy('name');

        return $this->showFaqs($faq, ['tags' => $tags]);
    }

    public function tagged(Request $request): Response
    {
        $tagId = $request->getAttribute('tag_id');
        $tag = Tag::findOrFail($tagId);

        $faq = $tag->faqs()
            ->with('tags')
            ->orderBy('question')
            ->get();
        if ($faq->isEmpty()) {
            throw new HttpNotFound();
        }

        return $this->showFaqs($faq, ['tag' => $tag]);
    }

    protected function showFaqs(Collection $faqs, array $data): Response
    {
        $text = $this->config->get('faq_text');

        return $this->response->withView(
            'pages/faq/index',
            array_merge(['text' => $text, 'items' => $faqs], $data)
        );
    }
}
