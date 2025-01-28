<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Tag;
use Psr\Log\LoggerInterface;

class TagController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'tag.edit',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected Tag $tag,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function list(): Response
    {
        $items = $this->tag->all();
        return $this->response->withView(
            'pages/tag/index.twig',
            ['items' => $items]
        );
    }

    public function edit(Request $request): Response
    {
        $tagId = $request->getAttribute('tag_id'); // optional
        $tag = $this->tag->find($tagId);

        return $this->showEdit($tag);
    }

    public function save(Request $request): Response
    {
        $tagId = $request->getAttribute('tag_id'); // optional

        /** @var Tag $tag */
        $tag = $this->tag->findOrNew($tagId);

        if ($request->request->has('delete')) {
            return $this->delete($tag);
        }

        $data = $this->validate($request, [
            'name' => 'required|max:255',
            'delete' => 'optional|checked',
        ]);

        $tag->name = $data['name'];

        if (
            $this->tag
                ->where('name', $tag->name)
                ->whereNot('id', $tag->id)
                ->exists()
        ) {
            $this->addNotification('tag.edit.duplicate', NotificationType::ERROR);

            return $this->showEdit($tag);
        }

        $tag->save();

        $this->log->info('Saved tag "{name}" ({id})', ['name' => $tag->name, 'id' => $tag->id]);
        $this->addNotification('tag.edit.success');

        return $this->redirect->to('/admin/tags');
    }

    protected function delete(Tag $tag): Response
    {
        $tag->delete();

        $this->log->info('Deleted tag "{name}" ({id})', ['name' => $tag->name, 'id' => $tag->id]);
        $this->addNotification('tag.delete.success');

        return $this->redirect->to('/admin/tags');
    }

    protected function showEdit(?Tag $tag): Response
    {
        return $this->response->withView(
            'pages/tag/edit.twig',
            ['tag' => $tag]
        );
    }
}
