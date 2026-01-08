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
        /** @var ShiftType $shiftType */
        $shiftType = $this->shiftType->findOrFail($shiftTypeId);

        $days = $shiftType->shifts()
            ->scopes('needsUsers')
            ->selectRaw('DATE(start) AS date')
            ->orderBy('date')
            ->groupBy('date')
            ->pluck('date');

        $day = $request->get('day');
        $day = $days->contains($day) ? $day : $days->first();

        $shifts = $shiftType->shifts()
            ->with([
                'neededAngelTypes.angelType',
                'schedule',
                'tags',
                'shiftEntries.user.personalData',
                'shiftEntries.user.state',
                'shiftEntries.angelType',
                'shiftType.neededAngelTypes.angelType',
                'location.neededAngelTypes.angelType',
            ])
            ->whereDate('start', $day)
            ->orderBy('start')
            ->get();

        return $this->response->withView(
            'admin/shifttypes/view',
            [
                'shifttype' => $shiftType,
                'is_view' => true,
                'shifts_active' => $request->has('shifts') || $request->get('day'),
                'days' => $days,
                'selected_day' => $day,
                'shifts' => $shifts,
            ]
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
                'work_category' => 'optional|in:A,B,C',
                'allows_accompanying_children' => 'optional|checked',
            ] + $validation
        );

        if (ShiftType::whereName($data['name'])->where('id', '!=', $shiftType->id)->exists()) {
            throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
        }

        $shiftType->name = $data['name'];
        $shiftType->description = $data['description'] ?? '';
        $shiftType->signup_advance_hours = $data['signup_advance_hours'] ?: null;
        $shiftType->work_category = $data['work_category'] ?? 'A';
        $shiftType->allows_accompanying_children = (bool) ($data['allows_accompanying_children'] ?? false);

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
            'Saved shift type "{name}" ({id}): {description}, {signup_advance_hours}, ' .
            'work_category={work_category}, allows_children={allows_children}, {angels}',
            [
                'id' => $shiftType->id,
                'name' => $shiftType->name,
                'description' => $shiftType->description,
                'signup_advance_hours' => $shiftType->signup_advance_hours,
                'work_category' => $shiftType->work_category,
                'allows_children' => $shiftType->allows_accompanying_children ? 'yes' : 'no',
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
