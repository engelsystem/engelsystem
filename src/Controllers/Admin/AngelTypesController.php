<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
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
        $angelType = $this->getAngelType($request);
        $canEdit = ($angelType && $this->auth->user()?->isAngelTypeSupporter($angelType))
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
        $isSupporter = $angelType &&
            $this->auth->user()->isAngelTypeSupporter($angelType) && !$this->auth->can('angeltypes.edit');

        return $this->response->withView(
            'admin/angeltypes/edit',
            [
                'angelType' => $angelType,
                'isSupporter' => $isSupporter,
            ]
        );
    }

    public function save(Request $request): Response
    {
        $angelTypeId = (int) $request->getAttribute('angel_type_id');
        $new = false;

        /** @var AngelType $angelType */
        $angelType = $this->angelType->find($angelTypeId);

        if (!$angelType) {
            if ($this->auth->can('angeltypes.edit')) {
                $angelType = new AngelType();
                $new = true;
            } else {
                throw_redirect('/angeltypes');
            }
        }

        $validation = [];

        $data = $this->validate(
            $request,
            [
                'name' => 'required|max:255',
                'description' => 'optional',
                'contact_name' => 'optional|max:255',
                'contact_dect' => 'optional|max:255',
                'contact_email' => 'optional|max:255',
                'restricted' => 'optional|checked',
                'shift_self_signup' => 'optional|checked',
                'show_on_dashboard' => 'optional|checked',
                'hide_register' => 'optional|checked',
                'hide_on_shift_view' => 'optional|checked',
                'requires_driver_license' => 'optional|checked',
                'requires_ifsg_certificate' => 'optional|checked',
            ] + $validation
        );

        if ($this->auth->can('angeltypes.edit')) {
            if (AngelType::whereName($data['name'])->where('id', '!=', $angelType->id)->exists()) {
                throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
            }
            $angelType->name = $data['name'];
            $angelType->restricted = (bool) $data['restricted'];
            $angelType->shift_self_signup = (bool) $data['shift_self_signup'];
            $angelType->show_on_dashboard = (bool) $data['show_on_dashboard'];
            $angelType->hide_register = (bool) $data['hide_register'];
            $angelType->hide_on_shift_view = (bool) $data['hide_on_shift_view'];
            $angelType->requires_driver_license = (bool) $data['requires_driver_license'];
            $angelType->requires_ifsg_certificate = (bool) $data['requires_ifsg_certificate'];
        }

        $angelType->description = (string) $data['description'];
        $angelType->contact_name = (string) $data['contact_name'];
        $angelType->contact_dect = (string) $data['contact_dect'];
        $angelType->contact_email = (string) $data['contact_email'];

        $angelType->save();

        $this->log->info(
            '{new} angel type "{name}" ({id}): {restricted} {shift_self_signup} {description}, '
            . '{contact_name} {contact_dect}{contact_email}, {show_on_dashboard}, {hide_register}, '
            . '{hide_on_shift_view}{requires_driver_license}{requires_ifsg}',
            [
                'new' => $new ? 'Created' : 'Updated',
                'id' => $angelType->id,
                'name' => $angelType->name,
                'restricted' => $angelType->restricted ? 'restricted ,' : '',
                'shift_self_signup' => $angelType->shift_self_signup ? 'shift_self_signup ,' : '',
                'description' => $angelType->description,
                'contact_name' => $angelType->contact_name,
                'contact_dect' => $this->config->get('enable_dect') ? $angelType->contact_dect . ', ' : '',
                'contact_email' => $angelType->contact_email,
                'show_on_dashboard' => $angelType->show_on_dashboard,
                'hide_register' => $angelType->hide_register,
                'hide_on_shift_view' => $angelType->hide_on_shift_view,
                'requires_driver_license' =>
                    $this->config->get('driving_license_enabled') && $angelType->requires_driver_license
                        ? ', requires driver license'
                        : '',
                'requires_ifsg' =>
                    $this->config->get('ifsg_enabled') && $angelType->requires_ifsg_certificate
                        ? ', requires ifsg certificate'
                        : '',
            ]
        );

        $this->addNotification('angeltype.edit.success');

        return $this->redirect->to('/angeltypes');
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

    protected function getAngelType(ServerRequestInterface $request): AngelType | null
    {
        $angelTypeId = (int) $request->getAttribute('angel_type_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::find($angelTypeId);
        return $angelType;
    }
}
