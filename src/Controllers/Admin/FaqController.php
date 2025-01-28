<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Tag;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class FaqController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'faq.view',
        'faq.edit',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected Faq $faq,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function edit(Request $request): Response
    {
        $faqId = $request->getAttribute('faq_id'); // optional

        $faq = $this->faq->with('tags')->find($faqId);

        return $this->showEdit($faq);
    }

    public function save(Request $request): Response
    {
        $faqId = $request->getAttribute('faq_id'); // optional

        /** @var Faq $faq */
        $faq = $this->faq->findOrNew($faqId);

        if ($request->request->has('delete')) {
            return $this->delete($faq);
        }

        $data = $this->validate($request, [
            'question' => 'required|max:255',
            'text'     => 'required',
            'tags'     => 'optional',
            'delete'   => 'optional|checked',
            'preview'  => 'optional|checked',
        ]);

        $faq->question = $data['question'];
        $faq->text = $data['text'];

        $tags = collect(explode(',', $data['tags'] ?? ''))
            ->transform(fn($value) => trim($value))
            ->filter(fn($value) => $value != '')
            ->unique();

        if (!is_null($data['preview'])) {
            $faq['tags'] = new Collection();
            foreach ($tags as $tagName) {
                $tag = new Tag(['name' => $tagName]);
                $faq['tags'][] = $tag;
            }

            return $this->showEdit($faq, $data['tags']);
        }

        $faq->save();

        $faq->tags()->detach();
        foreach ($tags as $tagName) {
            $tag = Tag::whereName($tagName)->firstOrCreate(['name' => $tagName]);
            $faq->tags()->attach($tag);
        }

        $this->log->info(
            'Saved faq "{question}" ({id}): {text}',
            ['question' => $faq->question, 'text' => $faq->text, 'id' => $faq->id]
        );

        $this->addNotification('faq.edit.success');

        return $this->redirect->to('/faq#faq-' . $faq->id);
    }

    protected function delete(Faq $faq): Response
    {
        $faq->delete();

        $this->log->info('Deleted faq "{question}" ({id})', ['question' => $faq->question, 'id' => $faq->id]);

        $this->addNotification('faq.delete.success');

        return $this->redirect->to('/faq');
    }

    protected function showEdit(?Faq $faq, ?string $tags = null): Response
    {
        return $this->response->withView(
            'pages/faq/edit.twig',
            ['faq' => $faq, 'tags' => $tags]
        );
    }
}
