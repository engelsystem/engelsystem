<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class ShiftTypesController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'shifttypes.view',
        'edit' => 'shifttypes.edit',
        'delete' => 'shifttypes.edit',
        'save' => 'shifttypes.edit',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected ShiftType $shiftType,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function index(): Response
    {
        $shiftTypes = $this->shiftType
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

        return $this->response->withView(
            'admin/shifttypes/index',
            ['shifttypes' => $shiftTypes, 'is_index' => true]
        );
    }

    public function edit(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shift_type_id');

        $shiftType = $this->shiftType->find($shiftTypeId);
        $angeltypes = AngelType::all()
            ->sortBy('name');

        return $this->response->withView(
            'admin/shifttypes/edit',
            [
                'shifttype' => $shiftType,
                'angel_types' => $angeltypes,
            ]
        );
    }

    public function view(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shift_type_id');
        $shiftType = $this->shiftType->findOrFail($shiftTypeId);

        return $this->response->withView(
            'admin/shifttypes/view',
            ['shifttype' => $shiftType, 'is_view' => true]
        );
    }

    public function save(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shift_type_id');

        /** @var ShiftType $shiftType */
        $shiftType = $this->shiftType->findOrNew($shiftTypeId);

        if ($request->request->has('delete')) {
            return $this->delete($request);
        }

        /** @var Collection|AngelType[] $angelTypes */
        $angelTypes = AngelType::all();
        $validation = [];
        foreach ($angelTypes as $angelType) {
            $validation['angel_type_' . $angelType->id] = 'optional|int';
        }

        $data = $this->validate(
            $request,
            [
                'name' => 'required|max:255',
                'description' => 'optional',
                'signup_advance_hours' => 'optional|float',
            ] + $validation
        );

        if (ShiftType::whereName($data['name'])->where('id', '!=', $shiftType->id)->exists()) {
            throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
        }

        $shiftType->name = $data['name'];
        $shiftType->description = $data['description'] ?? '';
        $shiftType->signup_advance_hours = $data['signup_advance_hours'] ?: null;

        $shiftType->save();
        $shiftType->neededAngelTypes()->delete();

        // Associate angel types with the shift type
        $angelsInfo = '';
        foreach ($angelTypes as $angelType) {
            $count = $data['angel_type_' . $angelType->id];
            if (!$count) {
                continue;
            }

            $neededAngelType = new NeededAngelType();

            $neededAngelType->shiftType()->associate($shiftType);
            $neededAngelType->angelType()->associate($angelType);

            $neededAngelType->count = $data['angel_type_' . $angelType->id];

            $neededAngelType->save();

            $angelsInfo .= sprintf(', %s: %s', $angelType->name, $count);
        }

        $this->log->info(
            'Saved shift type "{name}" ({id}): {description}, {signup_advance_hours}, {angels}',
            [
                'id' => $shiftType->id,
                'name' => $shiftType->name,
                'description' => $shiftType->description,
                'signup_advance_hours' => $shiftType->signup_advance_hours,
                'angels' => $angelsInfo,
            ]
        );

        $this->addNotification('shifttype.edit.success');

        return $this->redirect->to('/admin/shifttypes');
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'id' => 'required|int',
            'delete' => 'checked',
        ]);

        $shiftType = $this->shiftType->findOrFail($data['id']);

        $shifts = $shiftType->shifts;
        foreach ($shifts as $shift) {
            event('shift.deleting', ['shift' => $shift]);
        }
        $shiftType->delete();

        $this->log->info('Deleted shift type {name} ({id})', ['name' => $shiftType->name, 'id' => $shiftType->id]);
        $this->addNotification('shifttype.delete.success');

        return $this->redirect->to('/admin/shifttypes');
    }
}
