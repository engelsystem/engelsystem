<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AngelTypesController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'delete' => 'angeltypes.edit',
    ];
    public function __construct(
        protected Response $response,
        protected Config $config,
        protected Authenticator $auth,
        protected AngelType $angelType,
        protected LoggerInterface $log,
        protected Redirector $redirect,
    ) {
    }
    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        $canEdit = $this->auth->user()?->isAngelTypeSupporter($this->getAngelType($request))
            || $this->auth->can('angeltypes.edit');
        return match ($method) {
            'edit' => $canEdit,
            'save' => $canEdit,
            default => parent::hasPermission($request, $method),
        };
    }

    public function edit(Request $request): Response
    {
        $angelTypeId = (int) $request->getAttribute('angel_type_id');

        $angelType = $this->angelType->find($angelTypeId);

        return $this->showEdit($angelType);
    }

    protected function showEdit(?AngelType $angelType): Response
    {
        $isSupporter = $this->auth->user()->isAngelTypeSupporter($angelType) && !$this->auth->can('angeltypes.edit');

        return $this->response->withView(
            'admin/angeltypes/edit',
            [
                'angelType' => $angelType,
                'isSupporter' => $isSupporter,
            ]
        );
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'id'     => 'required|int',
            'delete' => 'checked',
        ]);

        $angelType = $this->angelType->findOrFail($data['id']);

        $shiftsEnties = $angelType->shiftEntries();
        foreach ($shiftsEnties as $entry) {
            event('shift.entry.deleting', ['entry' => $entry]);
        }
        $angelType->delete();

        $this->log->info('Deleted angel type {angelType}', ['angelType' => $angelType->name]);
        $this->addNotification('angeltype.delete.success');

        return $this->redirect->to('/angeltypes/new');
    }

    protected function getAngelType(ServerRequestInterface $request): AngelType
    {
        $angelTypeId = (int) $request->getAttribute('angel_type_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($angelTypeId);
        return $angelType;
    }
}
