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
use Psr\Log\LoggerInterface;

class AngelTypesController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'edit' => 'angeltypes.edit',
        'save' => 'angeltypes.edit',
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
    public function edit(): Response
    {
        $angelTypes = $this->angelType
            ->with(['userAngelTypes' => function ($query): void {
                $query->where('user_id', '=', auth()->user()->id);
            }])
            ->orderBy('name')
            ->get();

        return $this->response->withView(
            'pages/angeltypes/index',
            [
                'angelTypes' => $angelTypes,
                'is_index' => true,
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
}
