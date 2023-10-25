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
use Engelsystem\Models\Shifts\ShiftType;
use Psr\Log\LoggerInterface;

class ShiftTypesController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'shifttypes',
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

        return $this->response->withView(
            'admin/shifttypes/edit',
            ['shifttype' => $shiftType]
        );
    }

    public function view(Request $request): Response
    {
        $shiftTypeId = (int) $request->getAttribute('shift_type_id');
        $shiftType = $this->shiftType->findOrFail($shiftTypeId);

        return $this->response->withView(
            'admin/shifttypes/view',
            ['shifttype' => $shiftType]
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

        $data = $this->validate(
            $request,
            [
                'name' => 'required',
                'description' => 'required|optional',
            ]
        );

        if (ShiftType::whereName($data['name'])->where('id', '!=', $shiftType->id)->exists()) {
            throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
        }

        $shiftType->name = $data['name'];
        $shiftType->description = $data['description'];

        $shiftType->save();

        $this->log->info(
            'Updated shift type "{name}": {description}',
            [
                'name' => $shiftType->name,
                'description' => $shiftType->description,
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
            foreach ($shift->shiftEntries as $entry) {
                event('shift.entry.deleting', [
                    'user' => $entry->user,
                    'start' => $shift->start,
                    'end' => $shift->end,
                    'name' => $shift->shiftType->name,
                    'title' => $shift->title,
                    'type' => $entry->angelType->name,
                    'location' => $shift->location,
                    'freeloaded' => $entry->freeloaded,
                ]);
            }
        }
        $shiftType->delete();

        $this->log->info('Deleted shift type {name}', ['name' => $shiftType->name]);
        $this->addNotification('shifttype.delete.success');

        return $this->redirect->to('/admin/shifttypes');
    }
}
